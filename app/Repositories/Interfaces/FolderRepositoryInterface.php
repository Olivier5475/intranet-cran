<?php

namespace App\Repositories\Interfaces;

use App\Models\Folder;
use Illuminate\Support\Collection;
use App\Exception\{FolderNotFoundException, PersistenceException};

interface FolderRepositoryInterface
{
    /**
     * Récupère un dossier par son identifiant avec ses relations immédiates.
     *
     * @param int $id
     * @return Folder
     * @throws FolderNotFoundException
     */
    public function read(int $id): Folder;

    /**
     * Récupère les dossiers racines (sans parent) pour la barre latérale.
     *
     * @return Collection<int, Folder>
     */
    public function getRacineChildren(): Collection;

    /**
     * Récupère un dossier avec son contenu (fichiers/documents) filtré par état d'archivage.
     * Charge également les parents pour le fil d'Ariane.
     *
     * @param int $id
     * @param bool $archived
     * @return Folder
     */
    public function getFolderWithContents(int $id, bool $archived): Folder;

    /**
     * Récupère un dossier et remonte la chaîne des parents.
     *
     * @param int $id
     * @return Folder
     */
    public function getFolderWithParents(int $id): Folder;

    /**
     * Utilise une requête récursive (CTE) pour obtenir tous les IDs des dossiers descendants.
     * Très performant pour scoper une recherche dans une sous-arborescence.
     *
     * @param int $rootFolderId
     * @return array<int>
     */
    public function getDescendantFolderIds(int $rootFolderId): array;

    /**
     * Crée un nouveau dossier.
     *
     * @param array{name: string, parent_id?: int, user_id: int, color?: string, departements?: array} $data
     * @return Folder
     * @throws PersistenceException
     */
    public function create(array $data): Folder;

    /**
     * Met à jour les propriétés d'un dossier.
     *
     * @param int $id
     * @param array $data
     * @return Folder
     * @throws PersistenceException
     */
    public function update(int $id, array $data): Folder;

    /**
     * Archive un dossier (is_archived = true).
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restaure un dossier archivé (is_archived = false).
     *
     * @param int $folder_id
     * @return bool
     */
    public function restore(int $folder_id): bool;
}
