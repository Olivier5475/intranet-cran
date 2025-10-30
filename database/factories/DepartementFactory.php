<?php

namespace Database\Factories;

use App\Models\Departement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Departement>
 */
class DepartementFactory extends Factory
{
    /**
     * Le nom du modèle correspondant à la factory.
     *
     * @var string
     */
    protected $model = Departement::class;

    /**
     * Définir l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Génère un nom, par exemple "Informatique et Réseaux"
        $name = fake()->words(rand(2, 3), true);

        // Tente de générer des initiales à partir du nom
        // "Informatique et Réseaux" -> "IR"
        $initials = collect(explode(' ', $name))
            ->map(fn($word) => Str::upper(substr($word, 0, 1)))
            ->implode('');

        // Si les initiales sont trop courtes (ex: un seul mot), on génère des lettres aléatoires
        if (strlen($initials) < 2) {
            $initials = Str::upper(fake()->lexify('??')); // '??' sera remplacé par 2 lettres
        }

        return [
            'name' => $name,
            'initials' => $initials,
        ];
    }
}
