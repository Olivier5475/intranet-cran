<?php

namespace App\DTO;

readonly class FolderDTO {
    public function __construct(
        public int $id,
        public string $name,
        public ?string $color,
        public ?array $children = null,
        public ?string $created_at = null,
        public string $type = "folder",
    ) {}
}
