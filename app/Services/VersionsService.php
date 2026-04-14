<?php

namespace App\Services;

use App\DTO\VersionDTO;
use App\Exception\FileNotFoundException;
use App\Models\Document;
use App\Models\File;
use App\Models\Version;
use App\Repositories\Interfaces\AttachmentRepositoryInterface;
use App\Repositories\Interfaces\DocumentRepositoryInterface;
use App\Repositories\Interfaces\FilesRepositoryInterface;
use App\Services\Interfaces\MapDTOServiceInterface;
use App\Services\Interfaces\VersionsServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

readonly class VersionsService implements VersionsServiceInterface
{

    public function __construct(
        private FilesRepositoryInterface      $filesRepository,
        private DocumentRepositoryInterface   $documentsRepository,
        private AttachmentRepositoryInterface $attachmentsRepository,
        private MapDTOServiceInterface        $mapDTOService,
    ) { }

    public function restoreFromVersionId(int $versionId, string $modelString): void {
        if($modelString === "files") {
            $repository = $this->filesRepository;
            $model = File::class;
        } elseif($modelString === "documents") {
            $repository = $this->documentsRepository;
            $model = Document::class;
        } else {
            throw new BadRequestException();
        }

        try {
            DB::beginTransaction();
            $version = $repository->findVersionWithParent($versionId);
            if ($version->versionable_type !== $model) {
                throw new \Exception("La version ne concerne pas un fichier.");
            }

            $payload = $version->payload;
            $entity = $version->versionable;

            // Restauration physique si nécessaire
            if (isset($payload['archived_path'], $payload['storage_path'])) {
                if (!Storage::disk('public')->exists($payload['archived_path'])) {
                    throw new FileNotFoundException("Archive physique introuvable.");
                }
                Storage::disk('public')->copy($payload['archived_path'], $payload['storage_path']);
            }

            $attributes = collect($payload)->except(['archived_path', '_relations'])->toArray();

            // Restauration des relations (départements)
            if (isset($payload['_relations']['departements'])) {
                $attributes['departements'] = $payload['_relations']['departements'];
            }

            if (isset($payload['_relations']['attachments'])) {
                $doc = $repository->read($version->versionable_id);

                $currentIds = $doc->attachments()->pluck('id')->toArray();
                $history = collect($payload['_relations']['attachments']);
                $historyIds = $history->pluck('id')->toArray();

                // 1. Supprimer les attachments ajoutés entre-temps
                $idsToDelete = array_diff($currentIds, $historyIds);
                foreach ($idsToDelete as $idToDelete) {
                    $this->attachmentsRepository->delete($idToDelete);
                }

                // 2. Restaurer ceux qui manquent
                $idsToRestore = array_diff($historyIds, $currentIds);
                foreach ($history->whereIn('id', $idsToRestore) as $attachment) {
                    $attachment["document_id"] = $version->versionable_id;
                    $this->attachmentsRepository->create($attachment);
                }
            }

            $repository->update($entity->id, $attributes);

            DB::commit();
            Log::info("Version restaurée avec succès", ["model" => $model, "entity_id" => $entity->id, "version_id" => $versionId]);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec restauration version", ["version_id" => $versionId, "error" => $e->getMessage()]);
            throw $e;
        }
    }
    public function readVersionsFromParent(int $parent_id, string $modelString): array {
        if($modelString === "files") {
            $repository = $this->filesRepository;
        } elseif($modelString === "documents") {
            $repository = $this->documentsRepository;
        } else {
            throw new BadRequestException();
        }
        $versions = $repository->findVersionsFromParent($parent_id);
        return $versions->map(function ($version) {
            return $this->mapDTOService->mapToVersionDTO($version);
        })->toArray();
    }
}
