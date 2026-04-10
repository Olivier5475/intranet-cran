<?php

namespace App\Services;

use App\DTO\AttachmentDTO;
use App\DTO\DocumentViewDTO;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use App\Exception\ServerException;
use App\Models\Document;
use App\Services\Interfaces\AttachmentServiceInterface;
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mews\Purifier\Facades\Purifier;
use Parsedown;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Throwable;

readonly class DocumentService implements Interfaces\DocumentsServiceInterface {

    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
        private AttachmentServiceInterface $attachmentService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService,
    ){}

    public function read(int $id) : DocumentViewDTO {
        $document = $this->documentRepository->read($id);
        return $this->makeDocumentViewDto($document);
    }

    public function update(int $id, array $data): DocumentViewDTO {
        if(empty($id)) throw new BadRequestException("ID manquant");

        try {
            DB::beginTransaction();

            $newAttachments = $data["new_attachments"] ?? [];
            $existingAttachments = $data["existing_attachments"] ?? null;
            unset($data["new_attachments"], $data["existing_attachments"]);

            $document = $this->documentRepository->update($id, $data);

            // --- Synchronisation des Attachements ---
            if ($existingAttachments != null) {
                $currentIds = $document->attachments()->pluck('id')->all();
                $keepIds = collect($existingAttachments)->pluck('id')->all();

                // 1. Suppression de ce qui n'est plus listé
                foreach (array_diff($currentIds, $keepIds) as $deleteId) {
                    $this->attachmentService->delete($deleteId);
                }

                // 2. Mise à jour des existants
                foreach ($existingAttachments as $attachmentData) {
                    $attachId = $attachmentData["id"];
                    unset($attachmentData["id"]);

                    if ($this->attachmentService->getDocumentId($attachId) === $document->id) {
                        $this->attachmentService->update($attachId, $attachmentData);
                    } else {
                        throw new BadRequestException("Attachement non relié à ce document.");
                    }
                }
            }
            // 3. Ajout des nouveaux fichiers
            foreach ($newAttachments as $uploadedFile) {
                $this->attachmentService->create([
                    "document_id" => $document->id,
                    "uploaded_file" => $uploadedFile
                ]);
            }

            DB::commit();
            return $this->makeDocumentViewDto($document);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec de mise à jour transactionnelle du document", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function delete(int $id): bool {
        try {
            DB::beginTransaction();
//            $document = $this->documentRepository->read($id);

//            foreach ($document->attachments as $attachment) {
//                $this->attachmentService->delete($attachment->id);
//            }

            $result = $this->documentRepository->delete($id);
            DB::commit();

            return $result;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression complète du document", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function restore(int $document_id): bool
    {
        try {
            DB::beginTransaction();
//            $document = $this->documentRepository->read($id);

//            foreach ($document->attachments as $attachment) {
//                $this->attachmentService->delete($attachment->id);
//            }

            $result = $this->documentRepository->restore($document_id);
            DB::commit();

            return $result;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la suppression complète du document", ['id' => $document_id, 'error' => $e->getMessage()]);
            throw $e;
        }    }

    public function create(array $data): DocumentViewDTO {
        if(empty($data['name']) || empty($data['content'])) {
            throw new BadRequestException("Titre et contenu obligatoires.");
        }

        try {
            DB::beginTransaction();

            $newAttachments = $data["new_attachments"] ?? [];
            unset($data["new_attachments"], $data["existing_attachments"]);

            $data["user_id"] = $this->userService->getCurrentUserId();
            $document = $this->documentRepository->create($data);

            foreach ($newAttachments as $file) {
                $this->attachmentService->create([
                    "document_id" => $document->id,
                    "uploaded_file" => $file
                ]);
            }

            DB::commit();
            return $this->makeDocumentViewDto($document);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur création document", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function makeDocumentViewDto(Document $document) : DocumentViewDTO {
        $attachments = $document->attachments->map(fn($a) => new AttachmentDTO(
            id: $a->id,
            name: $a->name,
            storage_path: $a->storage_path,
            mimetype: $a->mimetype,
            size: $a->size,
        ))->all();

        // Rendu Markdown sécurisé
        $renderedHtml = (new Parsedown())->text($document->content);
        $cleanHtml = Purifier::clean($renderedHtml);

        return new DocumentViewDTO(
            id: $document->id,
            name: $document->name,
            content: $cleanHtml,
            attachments: $attachments,
            created_at: $document->created_at,
            updated_at: $document->updated_at,
            departements: $this->departementsService->departementsIDs($document->departements),
            folder_id: $document->folder_id,
            color: $document->color
        );
    }

    public function readRacineDoc(): ?DocumentViewDTO {
        try {
            $document = $this->documentRepository->readRacineDoc();
            return $document ? $this->makeDocumentViewDto($document) : null;
        } catch (Throwable $e) {
            Log::error("Erreur lecture document racine", ['error' => $e->getMessage()]);
            throw new ServerException("Impossible de charger le document d'accueil.");
        }
    }

    public function hasEditAccess(int $document_id): bool {
        $user = $this->userService->readById($this->userService->getCurrentUserId());
        $document = $this->documentRepository->read($document_id); // On utilise le repo direct pour la perf

        if($user->role === "admin" || empty($document->folder_id) || count($document->departements) === 0) {
            return true;
        }

        $docDeptIds = $this->departementsService->departementsIDs($document->departements);
        return (bool) array_intersect($user->departements, $docDeptIds);
    }
}
