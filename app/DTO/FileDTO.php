<?php

namespace App\DTO;

readonly class FileDTO {
    public function __construct(
        // Identifiant
        public int $id,

        // Informations
        public string $name,
        public string $created_at,
        public ?string $storage_path,
        public ?string $mimetype,
        public ?bool $is_archived = null,

        // Relations
        public array $departements,
        public int $folder_id,

        // type "file" pour que la vue sache qu'il s'agit d'un fichier
        public string $type = "file",
    ) {}
}
