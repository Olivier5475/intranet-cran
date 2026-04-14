<?php

namespace App\DTO;

readonly class DocumentDTO {
    public function __construct(
        // IDENTIFIANT
        public int $id,

        // CONTENU
        public string $name,
        public string $content,

        // RELATIONS
        public array $departements,
        public array $attachments,
        public ?int $folder_id,

        // INFORMATION
        public string $created_at,
        public string $updated_at,
        public ?string $color,
        public ?bool $is_archived = null,

        // Renvoie automatiquement le type en string,
        // pour savoir qu'il s'agit d'un document dans la vue
        public string $type = "document",
    ) {}
}
