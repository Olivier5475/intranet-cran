<?php

namespace App\Services\Interfaces;

use App\Models\{Folder, File, Document, Version, Departement, User, Attachment};
use App\DTO\{FolderDTO, FileDTO, DocumentDTO, VersionDTO, DepartementDTO, AuthDTO, AttachmentDTO};
use Illuminate\Support\Collection;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

interface MapDTOServiceInterface
{
    /**
     * Transforme un modèle Folder en FolderDTO.
     * Gère la récursion pour l'arborescence complète si demandé.
     *
     * @param Folder $folder
     * @param bool $withChildren Si true, charge récursivement les enfants chargés en relation.
     * @return FolderDTO
     */
    public function mapToFolderDTO(Folder $folder, bool $withChildren = false): FolderDTO;

    /**
     * Mappe tout le contenu d'un dossier (sous-dossiers, fichiers, documents) en une collection unique.
     * Les éléments sont triés par nom.
     *
     * @param Folder $folder
     * @return Collection<int, FolderDTO|FileDTO|DocumentDTO>
     */
    public function mapFolderContents(Folder $folder): Collection;

    /**
     * Transforme un modèle File en FileDTO.
     *
     * @param File $file
     * @return FileDTO
     */
    public function mapToFileDTO(File $file): FileDTO;

    /**
     * Transforme un modèle Document en DocumentDTO (inclut le rendu HTML et les attachements).
     *
     * @param Document $document
     * @return DocumentDTO
     */
    public function mapToDocumentDTO(Document $document): DocumentDTO;

    /**
     * Fusionne des fichiers et documents (souvent issus d'une recherche) en une collection de DTOs triée.
     *
     * @param Collection<int, File> $files
     * @param Collection<int, Document> $documents
     * @return Collection<int, FileDTO|DocumentDTO>
     */
    public function mapFilesAndDocuments(Collection $files, Collection $documents): Collection;

    /**
     * Transforme un modèle Version en VersionDTO.
     *
     * @param Version $version
     * @return VersionDTO
     */
    public function mapToVersionDTO(Version $version): VersionDTO;

    /**
     * Transforme un modèle Departement en DepartementDTO.
     *
     * @param Departement $departement
     * @return DepartementDTO
     */
    public function mapToDepartementDTO(Departement $departement): DepartementDTO;

    /**
     * Transforme une collection de départements en collection de DTOs.
     *
     * @param Collection<int, Departement> $departements
     * @return Collection<int, DepartementDTO>
     */
    public function mapToDepartementDTOsCollection(Collection $departements): Collection;

    /**
     * Transforme un modèle User ou un tableau de données utilisateur en AuthDTO.
     *
     * @param User|array $user
     * @return AuthDTO
     */
    public function mapToAuthDTO(User|array $user): AuthDTO;

    /**
     * Transforme une liste d'utilisateurs en collection de AuthDTOs.
     *
     * @param iterable $users Collection ou tableau d'utilisateurs.
     * @return Collection<int, AuthDTO>
     */
    public function mapToAuthDTOsCollection(iterable $users): Collection;

    /**
     * Transforme un modèle Attachment en AttachmentDTO.
     *
     * @param Attachment $attachment
     * @return AttachmentDTO
     * @throws FileNotFoundException Si le fichier n'est plus présent sur le disque.
     */
    public function mapToAttachmentDTO(Attachment $attachment): AttachmentDTO;
}
