<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'user_id' => \App\Models\User::factory(),
            'parent_id' => null, // Par défaut, c'est un dossier racine
        ];
    }
}
