<?php

namespace App\Services;

use App\DTO\{FolderDTO, FileDTO, DocumentDTO, VersionDTO, DepartementDTO, AuthDTO, AttachmentDTO};
use App\Models\{Folder, File, Document, Version, Departement, User, Attachment};
use App\Services\Interfaces\MapDTOServiceInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use Parsedown;

readonly class MapDTOService implements MapDTOServiceInterface
{
    // --- SECTION : DOSSIERS & NAVIGATION ---

    /**
     * @inheritDoc
     */
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
            departements: $this->getDeptIds($folder), // On passe l'objet entier ici
            color: $folder->color,
            children: $children,
            created_at: $folder->created_at,
            is_archived: $folder->is_archived ?? false
        );
    }

    /**
     * @inheritDoc
     */
    public function mapFolderContents(Folder $folder): Collection
    {
        $items = collect();

        $items = $items->concat($folder->children->map(fn($c) => $this->mapToFolderDTO($c)));
        $items = $items->concat($folder->files->map(fn($f) => $this->mapToFileDTO($f)));
        $items = $items->concat($folder->documents->map(fn($d) => $this->mapToDocumentDTO($d)));

        return $items->sortBy('name')->values();
    }

    /**
     * @inheritDoc
     */
    public function mapFilesAndDocuments(Collection $files, Collection $documents): Collection
    {
        $fileDTOs = $files->map(fn($f) => $this->mapToFileDTO($f));
        $docDTOs = $documents->map(fn($d) => $this->mapToDocumentDTO($d));

        return $fileDTOs->merge($docDTOs)->sortBy('name')->values();
    }

    // --- SECTION : CONTENU (FILES & DOCUMENTS) ---

    /**
     * @inheritDoc
     */
    public function mapToFileDTO(File $file): FileDTO
    {
        return new FileDTO(
            id: $file->id,
            name: $file->name,
            created_at: $file->created_at,
            storage_path: $file->storage_path,
            mimetype: $file->mimetype,
            is_archived: $file->is_archived ?? false,
            departements: $this->getDeptIds($file), // On passe l'objet entier
            folder_id: $file->folder_id,
        );
    }

    /**
     * @inheritDoc
     */
    public function mapToDocumentDTO(Document $document): DocumentDTO
    {
        // On n'appelle mapToAttachmentDTO que si la relation est chargée pour éviter les requêtes disque inutiles en liste
        $attachments = $document->relationLoaded('attachments')
            ? $document->attachments->map(fn($a) => $this->mapToAttachmentDTO($a))->all()
            : [];

        $html = (new Parsedown())->text($document->content ?? '');
        $cleanHtml = Purifier::clean($html);

        return new DocumentDTO(
            id: $document->id,
            name: $document->name,
            content: $cleanHtml,
            departements: $this->getDeptIds($document), // On passe l'objet entier
            attachments: $attachments,
            folder_id: $document->folder_id,
            created_at: $document->created_at,
            updated_at: $document->updated_at,
            color: $document->color,
            is_archived: $document->is_archived ?? false,
        );
    }

    // --- SECTION : ACTEURS (USERS & DEPT) ---

    /**
     * @inheritDoc
     */
    public function mapToAuthDTO(User|array $user): AuthDTO
    {
        if (is_array($user)) {
            return new AuthDTO(
                email: $user['email'],
                nom: $user['nom'],
                prenom: $user['prenom'],
                departements: is_array($user['departements']) ? $user['departements'] : [],
                role: $user['role'],
                id: $user['id']
            );
        }

        return new AuthDTO(
            email: $user->email,
            nom: $user->nom,
            prenom: $user->prenom,
            departements: $this->getDeptIds($user), // On passe l'objet entier
            role: $user->role,
            id: $user->id
        );
    }

    // ... (mapToVersionDTO, mapToDepartementDTO, mapToAttachmentDTO etc. restent identiques)

    /**
     * @inheritDoc
     */
    public function mapToVersionDTO(Version $version): VersionDTO
    {
        return new VersionDTO(
            id: $version->id,
            versionable_id: (int)$version->versionable_id,
            versionable_type: $version->versionable_type,
            payload: $version->payload
        );
    }

    /**
     * @inheritDoc
     */
    public function mapToAttachmentDTO(Attachment $attachment): AttachmentDTO
    {
        return new AttachmentDTO(
            id: $attachment->id,
            name: $attachment->name,
            storage_path: $attachment->storage_path,
            mimetype: $attachment->mimetype,
            size: $attachment->size
        );
    }

    /**
     * @inheritDoc
     */
    public function mapToDepartementDTO(Departement $departement): DepartementDTO
    {
        return new DepartementDTO(
            id: $departement->id,
            name: $departement->name,
            initials: $departement->initials,
            color: $departement->color
        );
    }

    /**
     * @inheritDoc
     */
    public function mapToDepartementDTOsCollection(Collection $departements): Collection
    {
        return $departements->map(fn($dept) => $this->mapToDepartementDTO($dept));
    }

    /**
     * @inheritDoc
     */
    public function mapToAuthDTOsCollection($users): Collection
    {
        return collect($users)->map(fn($user) => $this->mapToAuthDTO($user));
    }

    // --- HELPERS PRIVÉS OPTIMISÉS ---

    /**
     * Extrait les IDs de départements sans déclencher de nouvelle requête SQL.
     */
    private function getDeptIds(mixed $model): array
    {
        // Si c'est un modèle Eloquent (File, Document, Folder, User)
        if ($model instanceof \Illuminate\Database\Eloquent\Model) {
            // Crucial : On vérifie si la relation est déjà chargée
            if ($model->relationLoaded('departements')) {
                return $model->departements->pluck('id')->toArray();
            }
            // Si pas chargée, on ne fait RIEN (retourne vide) pour éviter le N+1
            return [];
        }

        return [];
    }
}
