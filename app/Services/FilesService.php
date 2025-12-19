<?php

namespace App\Services;

use App\DTO\DepartementDTO;
use App\DTO\FileDTO;
use App\Exception\DiskWriteException;
use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Models\File;
use App\Repositories\Interfaces\FilesRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
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

readonly class FilesService implements Interfaces\FilesServiceInterface {

    /**
     * @param FilesRepositoryInterface $filesRepository
     * @param FoldersServiceInterface $foldersService
     */
    public function __construct(
        private FilesRepositoryInterface $filesRepository,
        private FoldersServiceInterface $foldersService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService
    ){}

    public function create(array $data): void {
        if (empty($data["folder_id"]) || !($data['file'] ?? null) instanceof UploadedFile) {
            throw new BadRequestException("Missing folder ID or uploaded file.");
        }

        $uploadedFile = $data["file"];

        $folders = $this->foldersService->getBreadcrumbs($data["folder_id"]);

        $folder_path = "";
        foreach ($folders as $folder) {
            if ($data['folder_id'] === $folder->id) {
                $folder_path = $folder_path . $folder->id;
            } else {
                $folder_path = $folder_path . $folder->id . "/";
            }
        }

        $secure_name = Str::uuid() . "." . $uploadedFile->getClientOriginalExtension();

        try {
            $storage_path = $uploadedFile->storeAs($folder_path, $secure_name, "public");
        } catch (Throwable $e) {
            Log::warning("File upload failed" , [
                "message" => $e->getMessage(),
                "folder_path" => $folder_path,
            ]);
            throw new DiskWriteException();
        }

        $data["user_id"] = $this->userService->getCurrentUserId();
        $data['storage_path'] = $storage_path;
        if(empty($data["name"])) {
            $data['name'] = $uploadedFile->getClientOriginalName(); // Nom affiché à l'utilisateur
        }
        $data['mimetype'] = $uploadedFile->getMimeType();
        $data['size'] = $uploadedFile->getSize();

        unset($data['file']); // on ne passe pas le fichier au repository

        try {
            DB::beginTransaction();
            $this->filesRepository->create($data);
            DB::commit();
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

            Log::critical("Critical Error during file transaction.", [
                "message" => $e->getMessage(),
                "path" => $storage_path
            ]);

            throw new PersistenceException("A critical error occurred during data processing.", 0, $e);
        }
    }

    public function read($folder_id, $id) : FileDTO {
        if(empty($id)) {
            $e = new BadRequestException("Missing id.");
            Log::critical("File read failed: ", ["exception" => $e]);
            throw $e;
        }
        try {
            $file = $this->filesRepository->read($id);
            if($folder_id != $file->folder_id) {
                throw new FileNotFoundException();
            }
            return $this->makeFileDTO($file);
        } catch (FileNotFoundException $e) {
            Log::warning("File not found with id: " . $id);
            throw $e;
        } catch (Throwable $e) {
            Log::warning("File read failed: ", ["exception" => $e]);
            throw new FileNotFoundException();
        }
    }

    public function update(int $folder_id, int $id, array $data): void {
        if(empty($id) || empty($data["name"])) {
            throw new BadRequestException();
        }
        try {
            $file = $this->filesRepository->read($id);
        } catch (FileNotFoundException $e) {
            Log::warning("File not found with id: " . $id);
            throw $e;
        }

        if($folder_id != $file->folder_id ) {
            Log::warning("Bad folder_id : " . $folder_id);
            throw new FileNotFoundException("bad folder_id: " . $folder_id);
        }

        if(!empty($data["file"])) {
            $uploadedFile = $data["file"];

            $folders = $this->foldersService->getBreadcrumbs($data["folder_id"]);

            $folder_path = "";
            foreach ($folders as $folder) {
                if ($data['folder_id'] === $folder->id) {
                    $folder_path = $folder_path . $folder->id;
                } else {
                    $folder_path = $folder_path . $folder->id . "/";
                }
            }

            $secure_name = Str::uuid() . "." . $uploadedFile->getClientOriginalExtension();

            try {
                $storage_path = $uploadedFile->storeAs($folder_path, $secure_name, "public");
            } catch (Throwable $e) {
                Log::warning("File upload failed", [
                    "message" => $e->getMessage(),
                    "folder_path" => $folder_path,
                ]);
                throw new DiskWriteException();
            }

            $old_path = $file->storage_path;
            $data['storage_path'] = $storage_path;
            if(empty($data["name"])) {
                $data['name'] = $uploadedFile->getClientOriginalName(); // Nom affiché à l'utilisateur
            }
            $data['mimetype'] = $uploadedFile->getMimeType();
            $data['size'] = $uploadedFile->getSize();

            unset($data['file']);
        }
        try {
            DB::beginTransaction();
            $this->filesRepository->update($id, $data);
            if(!empty($uploadedFile)) {
                Storage::delete($old_path);
            }
            DB::commit();
        } catch (PersistenceException $e) {
            DB::rollBack();
            Log::warning("File attempted to update can\'t be persisted.: ", [
                "id" => $id,
                "data" => $data,
                "message" => $e->getMessage()
            ]);
            throw $e;
        } catch (FileNotFoundException $e) {
            DB::rollBack();
            Log::warning("File attempted to update was not found.: ", [
                "id" => $id,
                "data" => $data,
                "message" => $e->getMessage()
            ]);
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::warning("DB Transaction failed: ", []);
            throw new PersistenceException();
        }
    }

    public function delete(int $folder_id, int $id): bool {
        if(empty($id)) {
            throw new BadRequestException("Missing argument", 400);
        }

        try {
            $file = $this->filesRepository->read($id);
            if($file->folder_id != $folder_id) {
                throw new FileNotFoundException();
            }
            $storage_path = $file->storage_path;

            DB::beginTransaction();

            $success =  $this->filesRepository->delete($id);
            Storage::disk('public')->delete($storage_path);

            DB::commit();
            return $success;
        } catch (FileNotFoundException $e) {
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
            Log::warning("File attempted to delete can't be persisted.: ", [
                "id" => $id,
                "message" => $e->getMessage()
            ]);

            if ($e instanceof PersistenceException) {
                throw $e;
            }
            throw new PersistenceException("An unexpected error occurred during file deletion.", 0, $e);
        }
    }

    /**
     * Créé un FileDTO à partir d'un File
     * @param File $file
     * @return FileDTO
     */
    private function makeFileDTO(File $file): FileDTO {
        return new FileDTO(
            id: $file->id,
            name: $file->name,
            departements: $this->departementsService->departementsIDs($file->departements),
            created_at: $file->created_at,
            storage_path: $file->storage_path,
            mimetype: $file->mimetype,
        );
    }

    public function download(int $id): StreamedResponse {
        $file = $this->filesRepository->read($id);
        if (empty($file->storage_path)) {
            throw new Filesystem\FileNotFoundException("Empty path");
        }
        return Storage::disk('public')->download($file->storage_path, $file->name);
    }

}
