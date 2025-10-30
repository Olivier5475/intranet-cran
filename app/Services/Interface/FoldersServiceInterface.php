<?php

namespace App\Services\Interface;

use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use Illuminate\Support\Collection;

interface FoldersServiceInterface {

    /**
     * Récupère les enfants (dossiers, fichiers, documents) du dossier courant.
     * @return array<FolderDTO|FileDTO|DocumentDTO>
     */
    public function getChildren(int $id) : array;

    /**
     * Récupère le fil d'Ariane (tous les parents) pour un dossier.
     *
     * @return array<FolderDTO>
     */
    public function getBreadcrumbs(int $id): array;

    public function getRacineChildren() : array;


    /**
     * Récupère le contenu d'un dossier (navigation ou recherche).
     *
     * @param int $folderId L'ID du dossier courant
     * @param string|null $searchQuery Le terme de recherche (optionnel)
     * @return Collection Une collection de DTOs
     */
    public function getFolderContents(int $folderId, ?string $searchQuery): Collection;}
