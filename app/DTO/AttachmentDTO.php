<?php

namespace App\DTO;

readonly class AttachmentDTO {
    public function __construct(
        public string $id,
        public string $name,
        public string $storage_path,
        public string $mime_type,
        public string $size,
    ) { }
}
