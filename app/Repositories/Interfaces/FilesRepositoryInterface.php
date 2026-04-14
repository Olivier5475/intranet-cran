<?php

namespace App\Repositories\Interfaces;

use App\Models\{File, Version};
use Illuminate\Database\Eloquent\Collection;
use App\Exception\{AlreadyExistsException, FileNotFoundException, PersistenceException};

interface FilesRepositoryInterface
{
    /**
     * Récupère un fichier par son identifiant.
     * * @param int $id
     * @return File
     * @throws FileNotFoundException Si le fichier n'existe pas en base.
     */
    public function read(int $id): File;

    /**
     * Crée une entrée de fichier en base de données.
     * Vérifie l'unicité du nom dans le dossier cible.
     * * @param array{name: string, folder_id?: int, user_id: int, storage_path: string, mimetype: string, size: int, departements?: array} $data
     * @return File
     * @throws AlreadyExistsException Si un fichier ou document porte déjà ce nom.
     * @throws PersistenceException En cas d'erreur SQL.
     */
    public function create(array $data): File;

    /**
     * Met à jour les métadonnées d'un fichier.
     * * @param int $id
     * @param array $data
     * @return File
     * @throws FileNotFoundException
     * @throws AlreadyExistsException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): File;

    /**
     * Archive un fichier (suppression logique).
     * * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restaure un fichier archivé.
     * * @param int $file_id
     * @return bool
     */
    public function restore(int $file_id): bool;

    /**
     * Recherche des fichiers via Meilisearch/Scout dans un scope de dossiers.
     * * @param string $query Terme de recherche.
     * @param array $folderIds Scope des IDs de dossiers autorisés.
     * @param bool $fromArchived Recherche dans la corbeille.
     * @return Collection<int, File>
     */
    public function performSearch(string $query, array $folderIds, bool $fromArchived = false): Collection;

    /**
     * Récupère l'historique des versions pour un fichier.
     * * @param int $parent_id ID du fichier original.
     * @return Collection<int, Version>
     */
    public function findVersionsFromParent(int $parent_id): Collection;

    /**
     * Récupère une version spécifique.
     * * @param int $versionId
     * @return Version
     */
    public function findVersionWithParent(int $versionId): Version;
}
