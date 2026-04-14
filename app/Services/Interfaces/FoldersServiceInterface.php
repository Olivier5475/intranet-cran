<?php

namespace App\Services\Interfaces;

use App\DTO\FolderDTO;
use App\Exception\{FolderNotFoundException, PersistenceException};
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface FoldersServiceInterface
{
    /**
     * Récupère le contenu d'un dossier (fichiers, documents, sous-dossiers).
     * Bascule automatiquement sur la recherche si un terme est fourni.
     *
     * @param int $folderId ID du dossier cible.
     * @param string|null $searchQuery Terme de recherche optionnel.
     * @param bool $archived Indique s'il faut récupérer les éléments archivés.
     * @param bool|null $searchInContent Indique s'il faut chercher dans le contenu des documents.
     * @return array{items: \Illuminate\Support\Collection, breadcrumbs: array}
     */
    public function getFolderContents(int $folderId, ?string $searchQuery, bool $archived, ?bool $searchInContent): array;

    /**
     * Récupère l'arborescence complète (dossiers racines et leurs descendants).
     * Utilisé pour les composants de navigation latérale (Sidebar).
     *
     * @return array<int, FolderDTO> Tableau de DTOs avec récursion des enfants.
     */
    public function getRacineChildren(): array;

    /**
     * Génère la liste des dossiers parents pour construire le fil d'Ariane.
     *
     * @param int $id ID du dossier actuel.
     * @return array<int, FolderDTO> Liste ordonnée de la racine vers le dossier actuel.
     */
    public function getBreadcrumbs(int $id): array;

    /**
     * Récupère l'identifiant du dossier parent.
     *
     * @param int $folder_id
     * @return int
     */
    public function getParentId(int $folder_id): int;

    /**
     * Récupère un dossier spécifique par son identifiant.
     *
     * @param int $id
     * @return FolderDTO
     * @throws FolderNotFoundException Si le dossier n'existe pas.
     */
    public function read(int $id): FolderDTO;

    /**
     * Vérifie si l'utilisateur actuel a les droits d'édition sur le dossier.
     * Basé sur les départements rattachés ou le rôle administrateur.
     *
     * @param int $folder_id
     * @return bool
     */
    public function hasEditAccess(int $folder_id): bool;

    /**
     * Crée un nouveau dossier.
     *
     * @param array{name: string, color: string, parent_id?: int, user_id?: int} $data
     * @return FolderDTO
     * @throws BadRequestException Si le nom ou la couleur sont absents.
     * @throws PersistenceException Si la création en base de données échoue.
     */
    public function create(array $data): FolderDTO;

    /**
     * Met à jour les informations d'un dossier.
     *
     * @param int $id
     * @param array $data
     * @return FolderDTO
     * @throws BadRequestException Si l'ID est invalide.
     * @throws PersistenceException
     */
    public function update(int $id, array $data): FolderDTO;

    /**
     * Archive un dossier (suppression logique).
     *
     * @param int $id
     * @return void
     * @throws \Throwable
     */
    public function delete(int $id): void;

    /**
     * Restaure un dossier archivé.
     *
     * @param int $folder_id
     * @return void
     * @throws \Throwable
     */
    public function restore(int $folder_id): void;
}
