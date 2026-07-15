<?php

namespace App\DTO;

use Illuminate\Support\Collection;

readonly class GroupeDTO {

    public function __construct(
        public int $id,
        public string $name,
        public string $initials,
        public string $color,
        public ?Collection $users = null,
        public ?array $children = null,
        public ?int $parent = null,
    ) {}
}
