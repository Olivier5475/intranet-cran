<?php

namespace App\DTO;

readonly class AuthDTO {
    public function __construct(
        public string $email,
        public string $nom,
        public string $prenom,
        public array $departements,
        public ?string $role,
        public ?int $id,
    ) {}
}
