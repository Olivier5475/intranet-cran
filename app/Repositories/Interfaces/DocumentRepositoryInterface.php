<?php

namespace App\Repositories\Interfaces;

use App\Models\{Document, Version};
use Illuminate\Database\Eloquent\Collection;
use App\Exception\{AlreadyExistsException, DocumentNotFoundException, PersistenceException};

interface DocumentRepositoryInterface
{
    /**
     * Récupère un document par son identifiant.
     * * @param int $id
     * @return Document
     * @throws DocumentNotFoundException
     */
    public function read(int $id): Document;

    /**
     * Récupère le premier document sans parent (utilisé comme page d'accueil).
     * * @return Document|null
     */
    public function readRacineDoc(): ?Document;

    /**
     * Crée un nouveau document en vérifiant l'unicité du nom dans le dossier.
     * * @param array{name: string, content: string, folder_id?: int, user_id: int, color?: string, departements?: array} $data
     * @return Document
     * @throws AlreadyExistsException Si le nom est déjà pris (Fichier ou Document).
     * @throws PersistenceException
     */
    public function create(array $data): Document;

    /**
     * Met à jour un document existant.
     * * @param int $id
     * @param array $data
     * @return Document
     * @throws DocumentNotFoundException
     * @throws AlreadyExistsException
     * @throws PersistenceException
     */
    public function update(int $id, array $data): Document;

    /**
     * Archive un document (suppression logique).
     * * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restaure un document archivé.
     * * @param int $document_id
     * @return bool
     */
    public function restore(int $document_id): bool;

    /**
     * Recherche des documents via Meilisearch/Scout.
     * * @param string $query Terme de recherche.
     * @param array $folderIds Scope de dossiers (IDs).
     * @param bool $fromArchived Recherche dans la corbeille ou non.
     * @param bool $searchInContent Recherche dans le texte intégral.
     * @return Collection<int, Document>
     */
    public function performSearch(string $query, array $folderIds, bool $fromArchived = false, bool $searchInContent = false): Collection;

    /**
     * Récupère l'historique des versions pour un document donné.
     * * @param int $parent_id
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
