<?php

namespace App\Services;

use App\DTO\FileDTO;
use App\Exception\{DiskWriteException, PersistenceException};
use App\Repositories\Interfaces\FilesRepositoryInterface;
use App\Services\Interfaces\{
    DepartementsServiceInterface,
    FoldersServiceInterface,
    MapDTOServiceInterface,
    UserServiceInterface,
    FilesServiceInterface
};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Log, Storage};
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem\FileNotFoundException as FilesystemNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

readonly class FilesService implements FilesServiceInterface
{
    public function __construct(
        private FilesRepositoryInterface $filesRepository,
        private FoldersServiceInterface $foldersService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService,
        private MapDTOServiceInterface $mapDTOService,
    ){}

    // --- LECTURE & ACCÈS ---

    /**
     * @inheritDoc
     */
    public function read(int $id): FileDTO
    {
        return $this->mapDTOService->mapToFileDTO($this->filesRepository->read($id));
    }

    /**
     * @inheritDoc
     */
    public function hasEditAccess(int $file_id): bool
    {
        $user = $this->userService->readById($this->userService->getCurrentUserId());
        $file = $this->filesRepository->read($file_id);

        if ($user->role === "admin" || empty($file->folder_id) || count($file->departements) === 0) {
            return true;
        }

        $fileDeptIds = $this->departementsService->departementsIDs($file->departements);
        return (bool) array_intersect($user->departements, $fileDeptIds);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(int $file_id): int
    {
        return $this->filesRepository->read($file_id)->folder_id;
    }

    // --- TÉLÉCHARGEMENTS ---

    /**
     * @inheritDoc
     */
    public function download(int $id): StreamedResponse
    {
        $file = $this->filesRepository->read($id);
        if (!Storage::disk('public')->exists($file->storage_path)) {
            Log::error("Téléchargement impossible : fichier manquant", ["id" => $id, "path" => $file->storage_path]);
            throw new FilesystemNotFoundException("Fichier physique introuvable.");
        }
        return Storage::disk('public')->download($file->storage_path, $file->name);
    }

    /**
     * @inheritDoc
     */
    public function downloadVersion(int $id): StreamedResponse
    {
        $version = $this->filesRepository->findVersionWithParent($id);
        $payload = $version->payload;
        $path = $payload["archived_path"] ?? $payload["storage_path"] ?? null;

        if (!$path || !Storage::disk('public')->exists($path)) {
            throw new FilesystemNotFoundException("Fichier de version introuvable.");
        }

        return Storage::disk('public')->download($path, $payload["name"] ?? "version_".$id);
    }

    // --- ÉCRITURE ET GESTION PHYSIQUE ---

    /**
     * @inheritDoc
     */
    public function create(array $data): FileDTO
    {
        if (empty($data["folder_id"]) || !($data['file'] ?? null) instanceof \Symfony\Component\HttpFoundation\File\File) {
            throw new BadRequestException("ID dossier ou fichier manquant.");
        }

        $uploadedFile = $data["file"];
        $isUploaded = $uploadedFile instanceof UploadedFile;
        $originalName = $isUploaded ? $uploadedFile->getClientOriginalName() : $uploadedFile->getFilename();
        $fileExtension = $isUploaded ? $uploadedFile->getClientOriginalExtension() : $uploadedFile->getExtension();

        // Construction du chemin basé sur l'arborescence folders/ID/
        $breadcrumbs = $this->foldersService->getBreadcrumbs($data["folder_id"]);
        $folderPath = collect($breadcrumbs)->map(fn($f) => $f->id)->implode('/') . '/';
        $secureName = Str::uuid() . "." . $fileExtension;

        try {
            $storagePath = Storage::disk('public')->putFileAs($folderPath, $uploadedFile, $secureName);
            if (!$storagePath) throw new DiskWriteException();
        } catch (Throwable $t) {
            Log::error("Échec upload physique", ["path" => $folderPath, "error" => $t->getMessage()]);
            throw new DiskWriteException();
        }

        try {
            DB::beginTransaction();

            $data["user_id"] = $data["user_id"] ?? $this->userService->getCurrentUserId();
            $data['storage_path'] = $storagePath;
            $data['mimetype'] = $uploadedFile->getMimeType();
            $data['size'] = $uploadedFile->getSize();

            // suffixe l'extension si absente du nom d'affichage
            $displayName = $data["name"] ?? $originalName;
            $extension = "." . $fileExtension;
            $data["name"] = str_ends_with($displayName, $extension) ? $displayName : $displayName . $extension;

            unset($data['file']);
            $file = $this->filesRepository->create($data);

            DB::commit();
            return $this->mapDTOService->mapToFileDTO($file);

        } catch (Throwable $e) {
            DB::rollBack();
            Storage::disk('public')->delete($storagePath);
            Log::error("Échec persistance fichier", ["error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de l'enregistrement du fichier.", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): FileDTO
    {
        $file = $this->filesRepository->read($id);
        $oldPath = $file->storage_path;
        $newStoragePath = null;

        try {
            DB::beginTransaction();

            if (!empty($data["file"]) && $data["file"] instanceof UploadedFile) {
                $uploadedFile = $data["file"];
                $folderPath = dirname($oldPath) . '/';
                $secureName = Str::uuid() . "." . $uploadedFile->getClientOriginalExtension();

                $newStoragePath = $uploadedFile->storeAs($folderPath, $secureName, "public");
                $data['storage_path'] = $newStoragePath;
                $data['mimetype'] = $uploadedFile->getMimeType();
                $data['size'] = $uploadedFile->getSize();

                if (!empty($data["name"])) {
                    $ext = "." . $uploadedFile->getClientOriginalExtension();
                    if (!str_ends_with($data["name"], $ext)) $data["name"] .= $ext;
                }
                unset($data['file']);
            }

            $updatedFile = $this->filesRepository->update($id, $data);

            if ($newStoragePath) {
                Storage::disk('public')->delete($oldPath);
            }

            DB::commit();
            return $this->mapDTOService->mapToFileDTO($updatedFile);

        } catch (Throwable $e) {
            DB::rollBack();
            if ($newStoragePath) Storage::disk('public')->delete($newStoragePath);
            Log::error("Échec mise à jour fichier", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur de mise à jour.");
        }
    }

    // --- CYCLE DE VIE (LOGIQUE) ---

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        try {
            DB::beginTransaction();
            $res = $this->filesRepository->delete($id);
            DB::commit();
            return $res;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::critical("Échec archivage fichier", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de la suppression.");
        }
    }

    public function archive(int $file_id): bool {
        try {
            DB::beginTransaction();
            $res = $this->filesRepository->archive($file_id);
            DB::commit();
            return $res;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::critical("Échec archivage fichier", [
                "id" => $file_id,
                "error" => $e->getMessage()
            ]);
            throw new PersistenceException("Erreur lors de la suppression.");
        }
    }

    /**
     * @inheritDoc
     */
    public function restore(int $file_id): bool
    {
        try {
            DB::beginTransaction();
            $res = $this->filesRepository->restore($file_id);
            DB::commit();
            return $res;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::critical("Échec restauration fichier", ["id" => $file_id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de la restauration.");
        }
    }
}
