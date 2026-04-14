<?php

namespace App\Services;

use App\DTO\AttachmentDTO;
use App\Exception\{AttachmentNotFoundException, DiskWriteException, DocumentNotFoundException, PersistenceException};
use App\Repositories\Interfaces\{AttachmentRepositoryInterface, DocumentRepositoryInterface};
use App\Services\Interfaces\{AttachmentServiceInterface, FoldersServiceInterface, MapDTOServiceInterface};
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Log, Storage};
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

readonly class AttachmentService implements AttachmentServiceInterface
{
    public function __construct(
        private AttachmentRepositoryInterface $attachmentRepository,
        private FoldersServiceInterface $foldersService,
        private DocumentRepositoryInterface $documentsRepository,
        private MapDTOServiceInterface $mapDTOService,
    ){}

    // --- LECTURE ---

    public function read(int $id): AttachmentDTO
    {
        try {
            $attachment = $this->attachmentRepository->read($id);
            return $this->mapDTOService->mapToAttachmentDTO($attachment);
        } catch (AttachmentNotFoundException $e) {
            Log::alert("Attachment introuvable lors de la lecture", ["id" => $id]);
            throw $e;
        }
    }

    public function getDocumentId(int $attachment_id): int
    {
        return $this->attachmentRepository->read($attachment_id)->document_id;
    }

    // --- ECRITURE ---

    public function create(array $data): AttachmentDTO
    {
        if (empty($data["document_id"]) || !($data['uploaded_file'] ?? null) instanceof UploadedFile) {
            throw new BadRequestException("ID document ou fichier manquant.");
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $data["uploaded_file"];

        // 1. Construction du chemin sécurisé (Dossiers/Document_ID)
        $folderPath = "";
        try {
            $document = $this->documentsRepository->read($data["document_id"]);
            if ($document->folder_id) {
                $breadcrumbs = $this->foldersService->getBreadcrumbs($document->folder_id);
                foreach ($breadcrumbs as $folder) {
                    $folderPath .= $folder->id . "/";
                }
            }
            $folderPath .= "document_" . $document->id;
        } catch (DocumentNotFoundException $e) {
            Log::error("Création attachement impossible : document parent introuvable.", ["document_id" => $data["document_id"]]);
            throw $e;
        }

        // 2. Stockage physique (Nom UUID pour éviter les conflits et l'injection de nom)
        $secureName = Str::uuid() . "." . $uploadedFile->getClientOriginalExtension();
        $storagePath = $uploadedFile->storeAs($folderPath, $secureName, "public");

        if (!$storagePath) {
            Log::critical("Échec d'écriture disque pour l'attachement.", ["path" => $folderPath]);
            throw new DiskWriteException("Impossible d'écrire le fichier sur le disque.");
        }

        // 3. Persistance DB
        try {
            DB::beginTransaction();

            $attachment = $this->attachmentRepository->create([
                'document_id'  => $data['document_id'],
                'name'         => $uploadedFile->getClientOriginalName(),
                'storage_path' => $storagePath,
                'mimetype'     => $uploadedFile->getMimeType(),
                'size'         => $uploadedFile->getSize(),
            ]);

            DB::commit();

            return $this->mapDTOService->mapToAttachmentDTO($attachment);

        } catch (Throwable $e) {
            DB::rollBack();
            Storage::disk('public')->delete($storagePath); // Rollback physique si la DB échoue

            Log::error("Erreur de persistance attachement. Fichier supprimé.", [
                "error" => $e->getMessage(),
                "document_id" => $data["document_id"]
            ]);

            throw new PersistenceException("Erreur lors de l'enregistrement en base de données.", 0, $e);
        }
    }

    public function update(int $id, array $data): AttachmentDTO
    {
        try {
            // Vérification existence
            $this->attachmentRepository->read($id);

            $attachment = $this->attachmentRepository->update($id, $data);
            return $this->mapDTOService->mapToAttachmentDTO($attachment);

        } catch (AttachmentNotFoundException $e) {
            Log::warning("Tentative de mise à jour d'un attachement inexistant.", ["id" => $id]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("Erreur SQL lors de la mise à jour de l'attachement.", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur de mise à jour.");
        }
    }

    // --- ACTIONS ---

    public function download(int $id): StreamedResponse
    {
        $attachment = $this->attachmentRepository->read($id);

        if (!Storage::disk('public')->exists($attachment->storage_path)) {
            Log::error("Fichier absent du disque lors du téléchargement.", ["id" => $id, "path" => $attachment->storage_path]);
            throw new FileNotFoundException("Le fichier physique est introuvable.");
        }

        return Storage::disk('public')->response($attachment->storage_path, $attachment->name);
    }

    public function delete(int $id): bool
    {
        $attachment = $this->attachmentRepository->read($id);
        $path = $attachment->storage_path;

        try {
            DB::beginTransaction();

            $this->attachmentRepository->delete($id);
            Storage::disk('public')->delete($path);

            DB::commit();
            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::critical("Impossible de supprimer l'attachement (DB/Disque).", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de la suppression.");
        }
    }
}
