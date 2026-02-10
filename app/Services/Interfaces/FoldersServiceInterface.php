<?php

namespace App\Services\Interfaces;

use App\DTO\DocumentDTO;
use App\DTO\FileDTO;
use App\DTO\FolderDTO;
use App\Exception\DiskWriteException;
use App\Exception\FolderNotFoundException;
use App\Exception\PersistenceException;
use Illuminate\Support\Collection;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

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
     * @throws FolderNotFoundException si le folder n'existe pas
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
    public function getFolderContents(int $folderId, ?string $searchQuery): Collection;

    /**
     * @param int $id
     * @return FolderDTO
     * @throws FolderNotFoundException si le folder avec l'id n'est pas trouvé
     * @throws BadRequestException si il n'y a pas d'ID de transmis
     */
    public function read(int $id) : FolderDTO;

    /**
     * Fonction de création d'un Document
     * Vérifie les données
     * Lance la creation en BD avec le Repository*
     * Lance la creation attachment
     * @param array $data
     * @throws DiskWriteException
     * @throws FolderNotFoundException
     * @throws InternalErrorException
     * @throws PersistenceException
     */
    public function create(array $data) : FolderDTO;

    /**
     * @param int $id
     * @param array $data
     * @return FolderDTO|bool
     * @throws PersistenceException
     * @throws FolderNotFoundException
     * @throws Throwable
     */
    public function update(int $id, array $data) : FolderDTO|bool;

    /**
     * @param int $id
     * @return void
     * @throws BadRequestException si pas d'ID
     * @throws PersistenceException en cas d'erreur lors de la persistence en BD (throw par le Repository)
     * @throws FolderNotFoundException
     */
    public function delete(int $id) : void;

    /**
     * @param int $folder_id
     * @return bool
     */
    public function hasEditAccess(int $folder_id) : bool;
}
