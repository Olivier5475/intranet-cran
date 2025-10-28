<?php

namespace App\Interface;

use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;

interface FoldersServiceInterface {
    /**
     * Récupère le dossier parent du dossier courant.
     * Retourne null si c'est un dossier racine.
     * @return FolderDTO|null
     */
    public function getParent(int $id) : ?FolderDTO;

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
}
