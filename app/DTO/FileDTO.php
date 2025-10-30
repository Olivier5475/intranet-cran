<?php

namespace App\DTO;

readonly class FileDTO {
    public function __construct(
        public int $id,
        public string $name,
        public string $type = "file",
        public array $departements,
        public string $created_at
    ) {}
}
