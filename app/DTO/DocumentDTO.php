<?php

namespace App\DTO;

readonly class DocumentDTO {
    public function __construct(
        public int $id,
        public string $name,
        public string $type = "document",
        public array $departements,
        public string $created_at
    ) {}
}
