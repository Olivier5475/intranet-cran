<?php

namespace App\Services;

use App\DTO\FileDTO;
use App\Exception\DiskWriteException;
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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Contracts\Filesystem\FileNotFoundException as FilesystemNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

readonly class FilesService implements Interfaces\FilesServiceInterface {

    public function __construct(
        private FilesRepositoryInterface $filesRepository,
        private FoldersServiceInterface $foldersService,
        private UserServiceInterface $userService,
        private DepartementsServiceInterface $departementsService
    ){}

    public function create(array $data): FileDTO {
        if (empty($data["folder_id"]) || !($data['file'] ?? null) instanceof \Symfony\Component\HttpFoundation\File\File) {
            throw new BadRequestException("ID dossier ou fichier manquant.");
        }

        $uploadedFile = $data["file"];
        $isUploaded = $uploadedFile instanceof \Illuminate\Http\UploadedFile;
        $originalName = $isUploaded ? $uploadedFile->getClientOriginalName() : $uploadedFile->getFilename();
        $fileExtension = $isUploaded ? $uploadedFile->getClientOriginalExtension() : $uploadedFile->getExtension();

        // Construction simplifiée du chemin : folders/ID_1/ID_2/
        $breadcrumbs = $this->foldersService->getBreadcrumbs($data["folder_id"]);
        $folderPath = collect($breadcrumbs)->map(fn($f) => $f->id)->implode('/') . '/';

        $secureName = Str::uuid() . "." . $fileExtension;
        try {
            $storagePath = Storage::disk('public')->putFileAs(
                $folderPath,
                $uploadedFile,
                $secureName
            );
            if (!$storagePath) throw new DiskWriteException();
        } catch (Throwable $t) {
            Log::error("Échec upload fichier", ["path" => $folderPath, "error" => $t->getMessage()]);
            throw new DiskWriteException();
        }

        try {
            DB::beginTransaction();

            $data["user_id"] = $data["user_id"] ?? $this->userService->getCurrentUserId();
            $data['storage_path'] = $storagePath;
            $data['mimetype'] = $uploadedFile->getMimeType();
            $data['size'] = $uploadedFile->getSize();

            // Gestion du nom (priorité au nom saisi, sinon nom d'origine)
            $displayName = $data["name"] ?? $originalName;
            $extension = "." . $fileExtension;
            $data["name"] = str_ends_with($displayName, $extension) ? $displayName : $displayName . $extension;

            unset($data['file']);

            $file = $this->filesRepository->create($data);

            DB::commit();
            return $this->makeFileDTO($file);

        } catch (Throwable $e) {
            DB::rollBack();
            Storage::disk('public')->delete($storagePath);
            Log::error("Échec création fichier en BD. Nettoyage disque effectué.", ["error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de l'enregistrement du fichier.", 0, $e);
        }
    }

    public function update(int $id, array $data): FileDTO {
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

                // On s'assure que le nom garde l'extension
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
            return $this->makeFileDTO($updatedFile);

        } catch (Throwable $e) {
            DB::rollBack();
            if ($newStoragePath) Storage::disk('public')->delete($newStoragePath);
            Log::error("Échec mise à jour fichier", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur de mise à jour.");
        }
    }


    public function delete(int $id): bool {
//        $file = $this->filesRepository->read($id);
//        $path = $file->storage_path;

        try {
            DB::beginTransaction();
            $res = $this->filesRepository->delete($id);
//            Storage::disk('public')->delete($path);
            DB::commit();
            return $res;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::critical("Échec suppression fichier", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de la suppression.");
        }
    }

    public function restore(int $file_id): bool
    {
        try {
            DB::beginTransaction();
            $res = $this->filesRepository->restore($file_id);
            DB::commit();
            return $res;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::critical("Échec suppression fichier", ["id" => $id, "error" => $e->getMessage()]);
            throw new PersistenceException("Erreur lors de la suppression.");
        }
    }

    public function download(int $id): StreamedResponse {
        $file = $this->filesRepository->read($id);
        if (!Storage::disk('public')->exists($file->storage_path)) {
            Log::error("Téléchargement impossible : fichier manquant", ["id" => $id, "path" => $file->storage_path]);
            throw new FilesystemNotFoundException("Fichier physique introuvable.");
        }
        return Storage::disk('public')->download($file->storage_path, $file->name);
    }



    public function downloadVersion($id): StreamedResponse {
        $version = $this->filesRepository->findVersionWithParent($id);
        $payload = $version->payload;
        $path = $payload["archived_path"] ?? $payload["storage_path"] ?? null;

        if (!$path || !Storage::disk('public')->exists($path)) {
            throw new FilesystemNotFoundException("Fichier de version introuvable.");
        }

        return Storage::disk('public')->download($path, $payload["name"] ?? "version_".$id);
    }

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

    public function read(int $id): FileDTO {
        return $this->makeFileDTO($this->filesRepository->read($id));
    }

    public function hasEditAccess(int $file_id): bool {
        $user = $this->userService->readById($this->userService->getCurrentUserId());
        $file = $this->filesRepository->read($file_id);

        if($user->role === "admin" || empty($file->folder_id) || count($file->departements) === 0) {
            return true;
        }

        $fileDeptIds = $this->departementsService->departementsIDs($file->departements);
        return (bool) array_intersect($user->departements, $fileDeptIds);
    }
}
