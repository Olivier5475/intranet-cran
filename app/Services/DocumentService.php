<?php

namespace App\Services;

use App\DTO\DocumentDTO;
use App\Exception\{DocumentNotFoundException, ServerException};
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use App\Services\Interfaces\{
    AttachmentServiceInterface,
    DepartementsServiceInterface,
    MapDTOServiceInterface,
    UserServiceInterface,
    DocumentsServiceInterface
};
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class DocumentService implements DocumentsServiceInterface
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
        private AttachmentServiceInterface $attachmentService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService,
        private MapDTOServiceInterface $mapDTOService,
    ){}

    // --- LECTURE & ACCÈS ---

    /**
     * @inheritDoc
     */
    public function read(int $id): DocumentDTO
    {
        $document = $this->documentRepository->read($id);
        return $this->mapDTOService->mapToDocumentDTO($document);
    }

    /**
     * @inheritDoc
     */
    public function readRacineDoc(): ?DocumentDTO
    {
        try {
            $document = $this->documentRepository->readRacineDoc();
            return $document ? $this->mapDTOService->mapToDocumentDTO($document) : null;
        } catch (Throwable $e) {
            Log::error("Erreur lecture document racine", ['error' => $e->getMessage()]);
            throw new ServerException("Impossible de charger le document d'accueil.");
        }
    }

    /**
     * @inheritDoc
     */
    public function hasEditAccess(int $document_id): bool
    {
        $user = $this->userService->readById($this->userService->getCurrentUserId());
        $document = $this->documentRepository->read($document_id);

        // Accès complet pour admin ou document hors dossier (racine)
        if ($user->role === "admin" || empty($document->folder_id) || count($document->departements) === 0) {
            return true;
        }

        $docDeptIds = $this->departementsService->departementsIDs($document->departements);
        return (bool) array_intersect($user->departements, $docDeptIds);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(int $document_id): int
    {
        return $this->documentRepository->read($document_id)->folder_id;
    }

    // --- ÉCRITURE & GESTION DES FICHIERS ---

    /**
     * @inheritDoc
     */
    public function create(array $data): DocumentDTO
    {
        if (empty($data['name']) || empty($data['content'])) {
            throw new BadRequestException("Titre et contenu obligatoires.");
        }

        try {
            DB::beginTransaction();

            $newAttachments = $data["new_attachments"] ?? [];
            unset($data["new_attachments"], $data["existing_attachments"]);

            $data["user_id"] = $this->userService->getCurrentUserId();
            $document = $this->documentRepository->create($data);

            // Création des fichiers rattachés
            foreach ($newAttachments as $file) {
                $this->attachmentService->create([
                    "document_id" => $document->id,
                    "uploaded_file" => $file
                ]);
            }

            DB::commit();
            return $this->mapDTOService->mapToDocumentDTO($document);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur création document", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): DocumentDTO
    {
        if (empty($id)) throw new BadRequestException("ID manquant");

        try {
            DB::beginTransaction();

            $newAttachments = $data["new_attachments"] ?? [];
            $existingAttachments = $data["existing_attachments"] ?? null;
            unset($data["new_attachments"], $data["existing_attachments"]);

            $document = $this->documentRepository->update($id, $data);

            // --- Synchronisation des Attachements ---
            if ($existingAttachments !== null) {
                $currentIds = $document->attachments()->pluck('id')->all();
                $keepIds = collect($existingAttachments)->pluck('id')->all();

                // 1. Suppression des fichiers retirés
                foreach (array_diff($currentIds, $keepIds) as $deleteId) {
                    $this->attachmentService->delete($deleteId);
                }

                // 2. Mise à jour des meta-data (nom, etc.) des fichiers conservés
                foreach ($existingAttachments as $attachmentData) {
                    $attachId = $attachmentData["id"];
                    unset($attachmentData["id"]);

                    if ($this->attachmentService->getDocumentId($attachId) === $document->id) {
                        $this->attachmentService->update($attachId, $attachmentData);
                    } else {
                        throw new BadRequestException("L'attachement #$attachId n'appartient pas à ce document.");
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
            return $this->mapDTOService->mapToDocumentDTO($document);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec de mise à jour transactionnelle du document", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // --- SUPPRESSION & RESTAURATION ---

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        try {
            DB::beginTransaction();
            $result = $this->documentRepository->delete($id);
            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de l'archivage du document", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function restore(int $document_id): bool
    {
        try {
            DB::beginTransaction();
            $result = $this->documentRepository->restore($document_id);
            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Erreur lors de la restauration du document", ['id' => $document_id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
