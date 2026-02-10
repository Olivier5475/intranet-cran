<?php

namespace App\DTO;

class VersionDTO
{
    public function __construct(
        public int $id,
        public int $versionable_id,
        public string $versionable_type,
        public array $payload,
    ) {}
}
