<?php

namespace App\DTO;

readonly class DocumentViewDTO {
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public array $attachments,
        public string $created_at,
        public string $updated_at,
        public array $departements,
        public int $folder_id,
        public ?string $color = null,
    ) {}
}
