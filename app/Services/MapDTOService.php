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
            departements: $this->getDeptIds($folder->departements),
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
            departements: $this->getDeptIds($file->departements),
            folder_id: $file->folder_id,
        );
    }

    /**
     * @inheritDoc
     */
    public function mapToDocumentDTO(Document $document): DocumentDTO
    {
        $attachments = $document->attachments->map(fn($a) => $this->mapToAttachmentDTO($a))->all();

        // Rendu Markdown + Nettoyage XSS
        $html = (new Parsedown())->text($document->content ?? '');
        $cleanHtml = Purifier::clean($html);

        return new DocumentDTO(
            id: $document->id,
            name: $document->name,
            content: $cleanHtml,
            departements: $this->getDeptIds($document->departements),
            attachments: $attachments,
            folder_id: $document->folder_id,
            created_at: $document->created_at,
            updated_at: $document->updated_at,
            color: $document->color,
            is_archived: $document->is_archived ?? false,
        );
    }

    // --- SECTION : MÉTA-DONNÉES ---

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

    // --- SECTION : ACTEURS (USERS & DEPT) ---

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

    /**
     * @inheritDoc
     */
    public function mapToAuthDTOsCollection($users): Collection
    {
        return collect($users)->map(fn($user) => $this->mapToAuthDTO($user));
    }

    // --- HELPERS PRIVÉS ---

    /**
     * Extrait les IDs d'une collection de départements.
     * Accepte une Collection ou un tableau de données.
     */
    private function getDeptIds(mixed $departements): array
    {
        if ($departements instanceof Collection) {
            return $departements->pluck('id')->toArray();
        }

        // Cas des données brutes (AuthService)
        return array_map(fn($d) => is_array($d) ? $d['id'] : $d, (array)$departements);
    }
}
