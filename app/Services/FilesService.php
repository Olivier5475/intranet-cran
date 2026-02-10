<?php

namespace App\Services;

use App\DTO\FileDTO;
use App\DTO\VersionDTO;
use App\Exception\DiskWriteException;
use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use App\Models\File;
use App\Models\Version;
use App\Repositories\Interfaces\FilesRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem ;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

readonly class FilesService implements Interfaces\FilesServiceInterface {

    /**
     * @param FilesRepositoryInterface $filesRepository
     * @param FoldersServiceInterface $foldersService
     * @param UserServiceInterface $userService
     * @param DepartementsServiceInterface $departementsService
     */
    public function __construct(
        private FilesRepositoryInterface $filesRepository,
        private FoldersServiceInterface $foldersService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService
    ){}

    public function create(array $data): FileDTO {
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
        if (!str_ends_with($data["name"], "." . $uploadedFile->getClientOriginalExtension())) {
            $data["name"] = $data["name"] . "." . $uploadedFile->getClientOriginalExtension();
        }
        $data['mimetype'] = $uploadedFile->getMimeType();
        $data['size'] = $uploadedFile->getSize();

        unset($data['file']); // on ne passe pas le fichier au repository

        try {
            DB::beginTransaction();
            $file = $this->filesRepository->create($data);
            DB::commit();
            return $this->makeFileDTO($file);
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

    public function read($id) : FileDTO {
        if(empty($id)) {
            $e = new BadRequestException("Missing id.");
            Log::critical("File read failed: ", ["exception" => $e]);
            throw $e;
        }
        try {
            $file = $this->filesRepository->read($id);
            return $this->makeFileDTO($file);
        } catch (FileNotFoundException $e) {
            Log::warning("File not found with id: " . $id);
            throw $e;
        } catch (Throwable $e) {
            Log::warning("File read failed: ", ["exception" => $e]);
            throw new FileNotFoundException();
        }
    }

    public function update(int $id, array $data): FileDTO {
        if(empty($id)) {
            throw new BadRequestException();
        }
        try {
            $file = $this->filesRepository->read($id);
        } catch (FileNotFoundException $e) {
            Log::warning("File not found with id: " . $id);
            throw $e;
        }

        if(!empty($data["file"])) {
            $uploadedFile = $data["file"];

            $folders = $this->foldersService->getBreadcrumbs($file->folder_id);

            $folder_path = "";
            foreach ($folders as $folder) {
                if ($file->folder_id === $folder->id) {
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
            if (!str_ends_with($data["name"], "." . $uploadedFile->getClientOriginalExtension())) {
                $data["name"] = $data["name"] . "." . $uploadedFile->getClientOriginalExtension();
            }
            $data['mimetype'] = $uploadedFile->getMimeType();
            $data['size'] = $uploadedFile->getSize();

            unset($data['file']);
        }
        try {
            DB::beginTransaction();
            $file = $this->filesRepository->update($id, $data);
            if(!empty($uploadedFile)) {
                Storage::delete($old_path);
            }
            DB::commit();
            return $this->makeFileDTO($file);
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
            Log::warning("DB Transaction failed: ");
            throw new PersistenceException();
        }
    }

    public function delete(int $id): bool {
        if(empty($id)) {
            throw new BadRequestException("Missing argument", 400);
        }

        try {
            $file = $this->filesRepository->read($id);
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
            folder_id: $file->folder_id,
            storage_path: $file->storage_path,
            mimetype: $file->mimetype
        );
    }

    public function download(int $id): StreamedResponse {
        $file = $this->filesRepository->read($id);
        if (empty($file->storage_path)) {
            throw new Filesystem\FileNotFoundException("Empty path");
        }
        return Storage::disk('public')->download($file->storage_path, $file->name);
    }

    public function hasEditAccess(int $file_id): bool
    {
        $user = $this->userService->readById($this->userService->getCurrentUserId());
        $file = $this->read($file_id);
        if($user->role === "admin" || empty($file->folder_id) || $file->departements === []) {
            return true;
        }
        return (bool) array_intersect($user->departements, $file->departements);
    }

    public function restoreFromVersionId(int $versionId): void
    {
        // 1. On récupère la version et son parent
        $version = $this->filesRepository->findVersionWithParent($versionId);

        // 2. SÉCURITÉ TYPE
        if ($version->versionable_type !== File::class) {
            throw new Exception("Cette version ne correspond pas à un fichier.");
        }

        /** @var File $file */
        $file = $version->versionable;

        $oldData = $version->payload;

        // Logique physique : Si un fichier archivé existe, on écrase l'actuel
        if (isset($oldData['archived_path']) && isset($oldData['storage_path'])) {
            if (!Storage::disk('public')->exists($oldData['archived_path'])) {
                throw new Exception("Le fichier archivé est introuvable sur le disque.");
            }
            Storage::disk('public')->copy($oldData['archived_path'], $oldData['storage_path']);
        }

        $attributes = collect($oldData)->except(['archived_path', '_relations'])->toArray();

        if (isset($oldData['_relations']['departements'])) {
            $attributes['departements'] = $oldData['_relations']['departements'];
        }

        $this->filesRepository->update($file->id, $attributes);
    }

    public function readVersionsFromParent(int $parent_id): array
    {
        try {
            $versions = $this->filesRepository->findVersionsFromParent($parent_id);
            $dtos = [];
            foreach ($versions as $version) {
                $dtos[] = $this->makeVersionDTO($version);
            }
            return $dtos;
        } catch (Throwable $e) {
            Log::alert($e->getMessage(), ["exception" => $e]);
            throw $e;
        }
    }

    private function makeVersionDTO(Version $version): VersionDTO
    {
        return new VersionDTO(
            id: $version->id,
            versionable_id: intval($version->versionable_id),
            versionable_type: $version->versionable_type,
            payload: $version->payload
        );
    }

    public function downloadVersion($id): StreamedResponse {
        $file = $this->filesRepository->findVersionWithParent($id);
        $payload = $file->payload;
        if (empty($payload["storage_path"])) {
            throw new Filesystem\FileNotFoundException("Empty path");
        }
        Log::alert(Storage::disk('public')->download($payload["storage_path"], $payload["name"]));
        return Storage::disk('public')->download($payload["storage_path"], $payload["name"]);
    }
}
