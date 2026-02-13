<?php

namespace App\Services\Interfaces;

use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use App\Exception\DiskWriteException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface FoldersServiceInterface {

    /**
     * Récupère les enfants directs (dossiers, fichiers, documents).
     * @return array<FolderDTO|FileDTO|DocumentDTO>
     */
    public function getChildren(int $id) : array;

    /**
     * Récupère le fil d'Ariane.
     * @return FolderDTO[]
     * @throws FolderNotFoundException
     */
    public function getBreadcrumbs(int $id): array;

    /**
     * @return FolderDTO[]
     */
    public function getRacineChildren() : array;

    /**
     * Récupère le contenu d'un dossier (navigation ou recherche par pertinence via Scout).
     * @return Collection<FolderDTO|FileDTO|DocumentDTO>
     */
    public function getFolderContents(int $folderId, ?string $searchQuery): Collection;

    /**
     * @throws FolderNotFoundException|BadRequestException
     */
    public function read(int $id) : FolderDTO;

    /**
     * @throws PersistenceException|BadRequestException
     */
    public function create(array $data) : FolderDTO;

    /**
     * @throws PersistenceException|FolderNotFoundException|BadRequestException
     */
    public function update(int $id, array $data) : FolderDTO;

    /**
     * @throws FolderNotFoundException|PersistenceException|BadRequestException
     */
    public function delete(int $id) : void;

    /**
     * Vérifie les droits d'édition basés sur les départements.
     */
    public function hasEditAccess(int $folder_id) : bool;
}
