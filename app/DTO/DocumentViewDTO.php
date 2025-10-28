<?php

namespace App\DTO;

readonly class DocumentViewDTO {
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public array $attachments,
    ) {}
}
