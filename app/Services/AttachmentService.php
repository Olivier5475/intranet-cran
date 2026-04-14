<?php

namespace App\Services;

use App\DTO\AttachmentDTO;
use App\Exception\AttachmentNotFoundException;
use App\Exception\DiskWriteException;
use App\Exception\DocumentNotFoundException;
use App\Exception\PersistenceException;
use App\Models\Attachment;
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use App\Repositories\Interfaces\AttachmentRepositoryInterface;
use App\Services\Interfaces\MapDTOServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

readonly class AttachmentService implements Interfaces\AttachmentServiceInterface {

    public function __construct(
        private AttachmentRepositoryInterface $attachmentRepository,
        private FoldersServiceInterface $foldersService,
        private DocumentRepositoryInterface $documentsRepository,
        private MapDTOServiceInterface $mapDTOService,
    ){}

    public function create(array $data): AttachmentDTO {
        if (empty($data["document_id"]) || !($data['uploaded_file'] ?? null) instanceof UploadedFile) {
            throw new BadRequestException("ID document ou fichier manquant.");
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $data["uploaded_file"];

        // 1. Construction du chemin (Arborescence folders/document_ID/)
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

        // 2. Stockage physique
        $secureName = Str::uuid() . "." . $uploadedFile->getClientOriginalExtension();
        $storagePath = $uploadedFile->storeAs($folderPath, $secureName, "public");

        if (!$storagePath) {
            Log::critical("Échec d'écriture disque pour l'attachement.", ["path" => $folderPath]);
            throw new DiskWriteException("Impossible d'écrire le fichier sur le disque.");
        }

        // 3. Persistance DB
        try {
            DB::beginTransaction();

            $payload = [
                'document_id'  => $data['document_id'],
                'name'         => $uploadedFile->getClientOriginalName(),
                'storage_path' => $storagePath,
                'mimetype'     => $uploadedFile->getMimeType(),
                'size'         => $uploadedFile->getSize(),
            ];

            $attachment = $this->attachmentRepository->create($payload);
            DB::commit();

            return $this->mapDTOService->mapToAttachmentDTO($attachment);

        } catch (Throwable $e) {
            DB::rollBack();
            Storage::disk('public')->delete($storagePath); // Nettoyage du fichier orphelin

            Log::error("Erreur de persistance attachement. Fichier supprimé.", [
                "error" => $e->getMessage(),
                "document_id" => $data["document_id"]
            ]);

            throw new PersistenceException("Erreur lors de l'enregistrement en base de données.", 0, $e);
        }
    }

    public function read(int $id): AttachmentDTO {
        try {
            $attachment = $this->attachmentRepository->read($id);
        } catch (AttachmentNotFoundException $e) {
            Log::alert("Attachment introuvable", [
                "error" => $e->getMessage(),
                "id" => $id
            ]);
        }
        return $this->mapDTOService->mapToAttachmentDTO($attachment);
    }

    public function update(int $id, array $data): AttachmentDTO {
        try {
            // On vérifie l'existence avant
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

    public function delete(int $id): bool {
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

    public function download(int $id): StreamedResponse {
        $attachment = $this->attachmentRepository->read($id);

        if (!Storage::disk('public')->exists($attachment->storage_path)) {
            Log::error("Fichier absent du disque lors du téléchargement.", ["id" => $id, "path" => $attachment->storage_path]);
            throw new FileNotFoundException("Le fichier physique est introuvable.");
        }

        return Storage::disk('public')->response($attachment->storage_path, $attachment->name);
    }

    public function getDocumentId(int $attachment_id): int {
        return $this->attachmentRepository->read($attachment_id)->document_id;
    }
}
