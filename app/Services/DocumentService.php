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
use Purifier;
use Parsedown;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem;
use Throwable;

readonly class DocumentService implements Interfaces\DocumentsServiceInterface {

    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
        private AttachmentServiceInterface $attachmentService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService,
    ){}

    public function read($id) : DocumentViewDTO {
        try {
            $document = $this->documentRepository->read($id);
            return $this->makeDocumentViewDto($document);
        } catch (DocumentNotFoundException $e) {
            Log::warning("Document was not foud", [
                "id" => $id,
            ]);
            throw $e;
        }
    }


    public function update(int $id, array $data): DocumentViewDTO|bool {
        if(empty($id)) {
            throw new BadRequestException("Missing argument");
        }

        try {
            DB::beginTransaction();
            // 1. Extraction des attachments
            $new_attachment = $data["new_attachments"];
            unset($data["new_attachments"]); // ON LES RETIRE DES DATA

            $existing_attachments = $data["existing_attachments"];
            unset($data["existing_attachments"]); // ON LES RETIRE DES DATA

            // 2. Mise à jour du Document parent
            $document = $this->documentRepository->update($id, $data);

            if ($document instanceof Document) {
                // 3. Gestion des Attachments

                // IDs des attachements que l'utilisateur veut garder
                $idsToKeep = collect($existing_attachments)->pluck('id')->all();

                // IDs des attachements ACTUELLEMENT liés au document en BD
                $currentAttachmentIds = $document->attachments()->pluck('id')->all();

                // --- 3. Suppression des Attachements Retirés ---
                $idsToDelete = array_diff($currentAttachmentIds, $idsToKeep);
                foreach ($idsToDelete as $deleteId) {
                    $this->attachmentService->delete($deleteId); // Gère suppression BD et Disque
                }

                if (!empty($existing_attachments)) {
                    foreach ($existing_attachments as $attachment) {
                        $id = $attachment["id"]; unset($attachment["id"]);
                        if($this->attachmentService->getDocumentId($id) === $document->id) {
                            // Met à jour l'attachment existant (ou échoue)
                            $this->attachmentService->update($id, $attachment);
                        } else {
                            throw new BadRequestException("Attachment was not found in document", 400);
                        }
                    }
                    foreach ($new_attachment as $attachment) {
                        $attachment["document_id"] = $document->id;
                        $this->attachmentService->create($attachment);
                    }
                }

                DB::commit(); // Tout a réussi
                return $this->makeDocumentViewDto($document);
            }

            DB::commit(); // Rien n'a changé, on valide
            return $document;

        } catch (Throwable $e) { // Attrape toutes les erreurs BD ou Disque
            Log::warning("Transaction failed during document/attachment update.", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::error("Fatal error during rollback", []);
            }
            throw $e;
        }
    }

    public function delete(int $id): bool {
        try {
            $document = $this->documentRepository->read($id);
            foreach ($document->attachments() as $attachment) {
                $this->attachmentService->delete($attachment->id);
            }
            return $this->documentRepository->delete($id);
        } catch (DocumentNotFoundException $e) {
            Log::warning('Document attempted to delete was not found.', ['id' => $id]);
            throw $e;
        } catch (PersistenceException $e) {
            Log::warning('Document with ID'.$id.'can\'t be deleted', ['id' => $id]);
            throw $e;
        }
    }


    public function create(array $data): DocumentViewDTO {
        if(empty($data['title']) || empty($data['content'])) {
            throw new BadRequestException("Missing argument(s)");
        }

        try {
            $new_attachments = $data["new_attachments"];
            unset($data["new_attachments"]);
            unset($data["existing_attachments"]);

            $data["user_id"] = $this->userService->getCurrentUserId();

            DB::beginTransaction();
            if(!empty($data["folder_id"]) && is_string($data["folder_id"])) {
                $data['folder_id'] = intval($data["folder_id"]);
            }
            $document = $this->documentRepository->create($data);
            if($document instanceof Document && !empty($new_attachments)) {
                foreach ($new_attachments as $uploadedFile) {
                    $attachment_data = [
                        "document_id" => $document->id,
                        "uploaded_file" => $uploadedFile
                    ];
                    $this->attachmentService->create($attachment_data);
                }
            }
            DB::commit(); // Tout a réussi
            return $this->makeDocumentViewDto($document);
        } catch (PersistenceException $e) {
            Log::warning('Document attempted to create can\'t be persisted.', [
                "message" => $e->getMessage()
            ]);

            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::error("Fatal error during rollback", []);
            }

            throw $e;
        } catch (DiskWriteException|FolderNotFoundException $e) {
            Log::warning('Attachment attempted to create can\'t be persisted.');

            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::error("Fatal error during rollback", []);
            }

            throw $e;
        } catch (Throwable $e) {

            throw new InternalErrorException("UNEXPECTED FATAL ERROR. ".$e->getMessage());
        }
    }

    /**
     * Fonction qui génère
     * @param Document $document à partir d'un document
     * @return DocumentViewDTO un DocumentViewDTO
     * @throws Filesystem\FileNotFoundException si un fichier relié à un attachment n'est pas trouvé
     */
    private function makeDocumentViewDto(Document $document) : DocumentViewDTO {
        $attachments = [];
        foreach ($document->attachments as $attachment) {
            try {
            $attachments[] = new AttachmentDTO(
                id           : $attachment->id,
                name         : $attachment->name,
                storage_path : $attachment->storage_path,
                mimetype    : $attachment->mimetype,
                size         : $attachment->size,
            );
            } catch (Throwable $e) {
                Log::warning("File was not foud in storage", [
                    "storage_path" => $attachment->storage_path,
                ]);
                throw new Filesystem\FileNotFoundException("File not found.", 404);
            }
        }

        // 1. Initialiser Parsedown
        $parsedown = new Parsedown();

        // 2. Convertir le Markdown du contenu
        $renderedHtml = $parsedown->text($document->content);

        // 3. Sanitariser le HTML (Sécurité !)
        // Assurez-vous d'avoir bien importé la façade Purifier si vous l'utilisez
        $cleanHtml = Purifier::clean($renderedHtml);

        return new DocumentViewDTO(
            id : $document->id,
            title: $document->title,
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
            if(!empty($document)) {
                return $this->makeDocumentViewDto($document);
            }
            return null;
        } catch (Throwable $e) {
            Log::warning("Document attempted to read RacineDoc.");
            throw new ServerException("Erreur lors de la lecture du document racine");
        }
    }
}
