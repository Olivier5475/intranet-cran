<?php

namespace App\DTO;

readonly class FileDTO {
    public function __construct(
        public int $id,
        public string $name,
        public array $departements,
        public string $created_at,
        public int $folder_id,
        public ?string $storage_path,
        public ?string $mimetype,
        public ?bool $is_archived = null,
        public string $type = "file",
    ) {}
}
