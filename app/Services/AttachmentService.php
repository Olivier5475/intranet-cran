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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem ;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

readonly class AttachmentService implements Interfaces\AttachmentServiceInterface {

    public function __construct(
        private AttachmentRepositoryInterface $attachmentRepository,
        private FoldersServiceInterface $foldersService,
        private DocumentRepositoryInterface $documentsRepository,
    ){}

    /**
     * @inheritDoc
     */
    public function create(array $data): AttachmentDTO {
        if (empty($data["document_id"]) || !($data['uploaded_file'] ?? null) instanceof UploadedFile) {
            throw new BadRequestException("Missing document ID or uploaded file.");
        }

        $uploadedFile = $data["uploaded_file"];

        $folder_path = ""; // ON INIT LE CHEMIN

        try {
            $document = $this->documentsRepository->read($data["document_id"]);
        } catch (DocumentNotFoundException $e) {
            Log::warning("Document not found.");
            throw $e;
        }
        if($document->folder_id !== null) { // SI LE DOCUMENT N'EST PAS A LA RACINE
            $folders = $this->foldersService->getBreadcrumbs($document->folder_id);

            foreach ($folders as $folder) {
                $folder_path .= $folder->id . "/"; // ON CONSTRUIT LE CHEMIN
            }
        }

        $folder_path .= "document_".$data["document_id"]; // ON AJOUTE LA REF DU DOCUMENT DANS LE FOLDER

        $secure_name = Str::uuid() . "." . $uploadedFile->getClientOriginalExtension(); // ON GÉNÈRE UN NOM SÉCURISÉ

        try {
            $storage_path = $uploadedFile->storeAs($folder_path, $secure_name, "public"); // ON TENTE D'ENREGISTRER LE FICHIER
        } catch (FilesystemException|RuntimeException $e) {
            Log::warning("Attachement upload failed" , [ // ON LOG LES POTENTIELS ERREURS
                "message" => $e->getMessage(),
                "folder_path" => $folder_path,
            ]);
            throw new DiskWriteException(); // ON THROW UNE EXCEPTION PERSONNALISÉ
        }

        $data['name'] = $uploadedFile->getClientOriginalName(); // Nom affiché à l'utilisateur
        $data['storage_path'] = $storage_path;
        $data['mimetype'] = $uploadedFile->getMimeType();
        $data['size'] = $uploadedFile->getSize();

        unset($data['uploaded_file']); // on ne passe pas le fichier au repository

        try {
            DB::beginTransaction();
            $attachment = $this->attachmentRepository->create($data);
            DB::commit();
            return $this->makeAttachmentDTO($attachment);
        } catch (PersistenceException $e) {
            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::warning("Erreur avec le composant DB ");
            }
            Storage::disk('public')->delete($storage_path);

            Log::warning("File creation failed: ", [
                "message" => $e->getMessage()
            ]);
            throw $e;
        } catch (Throwable $e) {
            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::warning("Erreur avec le composant DB lors du rollback");
            }

            Storage::disk('public')->delete($storage_path);

            Log::critical("Critical Error during attachment transaction.", [
                "message" => $e->getMessage(),
                "path" => $storage_path
            ]);

            throw new PersistenceException("A critical error occurred during data processing.", previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(int $id): AttachmentDTO {
        try {
            $attachment = $this->attachmentRepository->read($id);
        } catch (AttachmentNotFoundException $e) {
            Log::warning("File not found" , [
                "id" => $id,
                "message" => $e->getMessage()
            ]);
            throw $e;
        }
        if(empty($attachment->storage_path)) {
            throw new Filesystem\FileNotFoundException();
        }
        // Accéder au fichier
        return $this->makeAttachmentDTO($attachment);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): AttachmentDTO {
        if(empty($id)) {
            throw new BadRequestException();
        }
        try {
            $attachment = $this->attachmentRepository->read($id);
            if (empty($data["name"])) {
                $data["name"] = $attachment->name;
            }
            $attachment = $this->attachmentRepository->update($id, $data);

            return $this->makeAttachmentDTO($attachment);
        } catch (PersistenceException $e) {
            Log::warning("File attempted to update can\'t be persisted.: ", [
                "id" => $id,
                "data" => $data,
                "message" => $e->getMessage()
            ]);
            throw $e;
        } catch (AttachmentNotFoundException $e) {
            Log::warning("File attempted to update was not found.: ", [
                "id" => $id,
                "data" => $data,
                "message" => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool {
        if(empty($id)) {
            throw new BadRequestException("Missing argument", 400);
        }

        try {
            $attachment = $this->attachmentRepository->read($id);
            $storage_path = $attachment->storage_path;

            DB::beginTransaction();

            $success =  $this->attachmentRepository->delete($id);
            Storage::disk('public')->delete($storage_path);

            DB::commit();
            return $success;
        } catch (AttachmentNotFoundException $e) {
            Log::warning("File attempted to delete was not found.: ", [
                "id" => $id,
                "message" => $e->getMessage()
            ]);
            throw $e;
        } catch (Throwable $e) {
            try {
                DB::rollBack();
            } catch (Throwable) {
                Log::warning("Erreur avec le composant DB lors du rollback");
            }
            Log::warning("Attachment attempted to delete can't be persisted.: ", [
                "id" => $id,
                "attachment_id" => $id,
                "message" => $e->getMessage()
            ]);

            if ($e instanceof PersistenceException) {
                throw $e;
            }
            throw new PersistenceException("An unexpected error occurred during attachment deletion.", 0, $e);
        }
    }

    /**
     * @throws Filesystem\FileNotFoundException
     */
    private function makeAttachmentDTO(Attachment $attachment) : AttachmentDTO {
        try {
            return new AttachmentDTO(
                id: $attachment->id,
                name: $attachment->name,
                storage_path: Storage::disk('public')->get($attachment->storage_path),
                mimetype: $attachment->mimetype,
                size: $attachment->size,
            );
        } catch (Throwable) {
            Log::warning("File read failed: ", [
                "storage_path" => $attachment->storage_path,
            ]);
            throw new Filesystem\FileNotFoundException();
        }
    }

    public function getDocumentId(int $attachment_id): int {
        return $this->attachmentRepository->read($attachment_id)->document_id;
    }

    public function download(int $id): StreamedResponse {
        $attachment = $this->attachmentRepository->read($id);
        if (empty($attachment->storage_path)) {
            throw new Filesystem\FileNotFoundException();
        }

        return Storage::disk('public')->download($attachment->storage_path, $attachment->name);
    }
}
