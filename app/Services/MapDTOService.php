<?php

namespace App\Services;

use App\DTO\{
    FolderDTO,
    FileDTO,
    DocumentDTO,
    VersionDTO,
    DepartementDTO,
    AuthDTO,
    AttachmentDTO
};
use App\Models\{Folder, File, Document, Version, Departement, User, Attachment};
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Services\Interfaces\{MapDTOServiceInterface, DepartementsServiceInterface};
use Illuminate\Support\Collection;
use Parsedown;
use Mews\Purifier\Facades\Purifier;

readonly class MapDTOService implements MapDTOServiceInterface
{

    public function mapToFolderDTO(Folder $folder, bool $withChildren = false): FolderDTO
    {
        $children = [];
        if ($withChildren && $folder->relationLoaded('allChildren')) {
            foreach ($folder->allChildren as $child) {
                $children[] = $this->mapToFolderDTO($child, true);
            }
        }

        return new FolderDTO(
            id: $folder->id,
            name: $folder->name,
            departements: $this->getDeptIds($folder->departements),
            color: $folder->color,
            children: $children,
            created_at: $folder->created_at,
            is_archived: $folder->is_archived ?? false
        );
    }

    // ----------------- FOLDERS -----------------
    public function mapFolderContents(Folder $folder): Collection
    {
        $items = collect();

        $items = $items->concat($folder->children->map(fn($c) => $this->mapToFolderDTO($c)));
        $items = $items->concat($folder->files->map(fn($f) => $this->mapToFileDTO($f)));
        $items = $items->concat($folder->documents->map(fn($d) => $this->mapToDocumentDTO($d)));

        return $items->sortBy('name')->values();
    }


    /**
     * Fusionne et mappe des fichiers et des documents pour les résultats de recherche
     */
    public function mapFilesAndDocuments(Collection $files, Collection $documents): Collection
    {
        $fileDTOs = $files->map(fn($f) => $this->mapToFileDTO($f));
        $docDTOs = $documents->map(fn($d) => $this->mapToDocumentDTO($d));

        return $fileDTOs
            ->merge($docDTOs)
            ->sortBy('name')
            ->values();
    }

    // --------------- FILES ---------------
    public function mapToFileDTO(File $file): FileDTO
    {
        return new FileDTO(
            // Identifiant
            id: $file->id,

            // Information
            name: $file->name,
            created_at: $file->created_at,
            storage_path: $file->storage_path,
            mimetype: $file->mimetype,
            is_archived: $file->is_archived ?? false,

            // Relations
            departements: $this->getDeptIds($file->departements),
            folder_id: $file->folder_id,
        );
    }

    // --------------- DOCUMENTS ---------------
    public function mapToDocumentDTO(Document $document): DocumentDTO
    {
        $attachments = $document->attachments->map(fn($a) => $this->mapToAttachmentDTO($a))->all();

        // Rendu HTML Sécurisé (pas d'injection de script)
        $html = (new Parsedown())->text($document->content ?? '');
        $cleanHtml = Purifier::clean($html);

        return new DocumentDTO(
            // IDENTIFIANT
            id: $document->id,

            // CONTENU
            name: $document->name,
            content: $cleanHtml,

            // RELATION
            departements: $this->getDeptIds($document->departements),
            attachments: $attachments,
            folder_id: $document->folder_id,

            // INFORMATION
            created_at: $document->created_at,
            updated_at: $document->updated_at,
            color: $document->color,
            is_archived: $document->is_archived ?? false,
        );
    }

    // --------------- VERSIONS ---------------
    public function mapToVersionDTO(Version $version): VersionDTO
    {
        return new VersionDTO(
            id: $version->id,
            versionable_id: (int)$version->versionable_id,
            versionable_type: $version->versionable_type,
            payload: $version->payload
        );
    }

    // ----------------- DEPARTEMENTS -----------------
    public function mapToDepartementDTO(Departement $departement): DepartementDTO
    {
        return new DepartementDTO(
            id: $departement->id,
            name: $departement->name,
            initials: $departement->initials,
            color: $departement->color
        );
    }

    public function mapToDepartementDTOsCollection(Collection $departements): Collection
    {
        return $departements->map(fn($dept) => $this->mapToDepartementDTO($dept));
    }

    /**
     * Helper interne pour éviter la répétition du pluck/toArray
     */
    private function getDeptIds(Collection $departements): array
    {
        return $departements->pluck('id')->toArray();
    }

    // ----------------- Users -----------------
    public function mapToAuthDTO(User|array $user): AuthDTO
    {
        if (is_array($user)) {
            return new AuthDTO(
                email: $user['email'],
                nom: $user['nom'],
                prenom: $user['prenom'],
                departements: $this->getDeptIds($user['departements']),
                role: $user['role'],
                id: $user['id']
            );
        }

        return new AuthDTO(
            email: $user->email,
            nom: $user->nom,
            prenom: $user->prenom,
            departements: $this->getDeptIds($user->departements),
            role: $user->role,
            id: $user->id
        );
    }

    public function mapToAuthDTOsCollection($users) : Collection
    {
        return collect($users)->map(fn($user) => $this->mapToAuthDTO($user));
    }

    public function mapToAttachmentDTO(Attachment $attachment): AttachmentDTO
    {
        if (!Storage::disk('public')->exists($attachment->storage_path)) {
            throw new FileNotFoundException("Fichier introuvable sur le disque : {$attachment->storage_path}");
        }

        return new AttachmentDTO(
            id: $attachment->id,
            name: $attachment->name,
            storage_path: $attachment->storage_path,
            mimetype: $attachment->mimetype,
            size: $attachment->size
        );
    }
}
