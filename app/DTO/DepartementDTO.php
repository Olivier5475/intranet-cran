<?php

namespace App\DTO;

readonly class DepartementDTO {

    public function __construct(
        public int $id,
        public string $name,
        public string $initials
    ) {}
}
