<?php

namespace App\Services\Interfaces;

use App\Models\{
    Folder,
    File,
    Document,
    Version,
    Departement,
    User,
    Attachment
};
use App\DTO\{
    FolderDTO,
    FileDTO,
    DocumentDTO,
    VersionDTO,
    DepartementDTO,
    AuthDTO,
    AttachmentDTO
};
use Illuminate\Contracts\Filesystem;
use Illuminate\Support\Collection;

interface MapDTOServiceInterface
{
    public function mapToFolderDTO(Folder $folder, bool $withChildren = false): FolderDTO;
    public function mapToFileDTO(File $file): FileDTO;
    public function mapToDocumentDTO(Document $document): DocumentDTO;
    public function mapToVersionDTO(Version $version): VersionDTO;
    public function mapToDepartementDTO(Departement $departement): DepartementDTO;
    public function mapToDepartementDTOsCollection(Collection $departements): Collection;
    public function mapToAuthDTO(User|array $user): AuthDTO;
    public function mapToAuthDTOsCollection(Collection $users): Collection;

    /**
     * @param Attachment $attachment
     * @return AttachmentDTO
     * @throws Filesystem\FileNotFoundException
     */
    public function mapToAttachmentDTO(Attachment $attachment): AttachmentDTO;

    /** @return Collection<int, FolderDTO|FileDTO|DocumentDTO> */
    public function mapFolderContents(Folder $folder): Collection;

    /**
     * @param Collection $files
     * @param Collection $documents
     * @return Collection
     */
    public function mapFilesAndDocuments(Collection $files, Collection $documents): Collection;

}
