<?php

namespace App\DTO;

readonly class DocumentDTO {
    public function __construct(
        public int $id,
        public string $name,
        public array $departements,
        public string $created_at,
        public ?string $color,
        public string $type = "document",
    ) {}
}
