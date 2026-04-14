<?php

namespace App\Services;

use App\DTO\VersionDTO;
use App\Exception\FileNotFoundException;
use App\Models\{Document, File, Version};
use App\Repositories\Interfaces\{AttachmentRepositoryInterface, DocumentRepositoryInterface, FilesRepositoryInterface};
use App\Services\Interfaces\{MapDTOServiceInterface, VersionsServiceInterface};
use Illuminate\Support\Facades\{DB, Log, Storage};
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

    // --- LECTURE ---

    /**
     * @inheritDoc
     */
    public function readVersionsFromParent(int $parent_id, string $modelString): array
    {
        $repository = $this->resolveRepository($modelString);

        $versions = $repository->findVersionsFromParent($parent_id);

        return $versions->map(fn($version) =>
        $this->mapDTOService->mapToVersionDTO($version)
        )->toArray();
    }

    // --- ACTIONS DE RESTAURATION ---

    /**
     * @inheritDoc
     */
    public function restoreFromVersionId(int $versionId, string $modelString): void
    {
        $repository = $this->resolveRepository($modelString);
        $modelClass = $modelString === "files" ? File::class : Document::class;

        try {
            DB::beginTransaction();

            $version = $repository->findVersionWithParent($versionId);

            if ($version->versionable_type !== $modelClass) {
                throw new \Exception("Incohérence : la version #$versionId ne correspond pas au type $modelString.");
            }

            $payload = $version->payload;
            $entity = $version->versionable;

            // 1. Restauration du fichier physique (si File)
            $this->restorePhysicalFile($payload);

            // 2. Préparation des attributs (exclusion des données techniques)
            $attributes = collect($payload)->except(['archived_path', '_relations'])->toArray();

            // 3. Restauration des relations simples (Départements)
            if (isset($payload['_relations']['departements'])) {
                $attributes['departements'] = $payload['_relations']['departements'];
            }

            // 4. Restauration des relations complexes (Attachments pour Documents)
            if (isset($payload['_relations']['attachments'])) {
                $this->syncAttachmentsFromHistory($version->versionable_id, $payload['_relations']['attachments']);
            }

            // 5. Mise à jour de l'entité via le repository
            $repository->update($entity->id, $attributes);

            DB::commit();
            Log::info("Version restaurée avec succès", [
                "model" => $modelString,
                "entity_id" => $entity->id,
                "version_id" => $versionId
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Échec restauration version", ["version_id" => $versionId, "error" => $e->getMessage()]);
            throw $e;
        }
    }

    // --- LOGIQUE PRIVÉE / HELPERS ---

    /**
     * Résout le repository adéquat selon le type demandé.
     */
    private function resolveRepository(string $modelString): FilesRepositoryInterface|DocumentRepositoryInterface
    {
        return match ($modelString) {
            "files" => $this->filesRepository,
            "documents" => $this->documentsRepository,
            default => throw new BadRequestException("Type de modèle non supporté : $modelString"),
        };
    }

    /**
     * Restaure le fichier sur le stockage si un chemin d'archive existe.
     */
    private function restorePhysicalFile(array $payload): void
    {
        if (isset($payload['archived_path'], $payload['storage_path'])) {
            if (!Storage::disk('public')->exists($payload['archived_path'])) {
                throw new FileNotFoundException("L'archive physique est introuvable sur le disque.");
            }
            Storage::disk('public')->copy($payload['archived_path'], $payload['storage_path']);
        }
    }

    /**
     * Synchronise les attachements du document avec l'état de l'historique.
     */
    private function syncAttachmentsFromHistory(int $documentId, array $historyAttachments): void
    {
        $doc = $this->documentsRepository->read($documentId);
        $currentIds = $doc->attachments()->pluck('id')->toArray();

        $history = collect($historyAttachments);
        $historyIds = $history->pluck('id')->toArray();

        // Suppression des attachements créés après cette version
        $idsToDelete = array_diff($currentIds, $historyIds);
        foreach ($idsToDelete as $idToDelete) {
            $this->attachmentsRepository->delete($idToDelete);
        }

        // Restauration des attachements qui existaient alors mais ont été supprimés depuis
        $idsToRestore = array_diff($historyIds, $currentIds);
        foreach ($history->whereIn('id', $idsToRestore) as $attachmentData) {
            $attachmentData["document_id"] = $documentId;
            $this->attachmentsRepository->create($attachmentData);
        }
    }
}
