<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'content' => fake()->paragraphs(3, true),
            'user_id' => \App\Models\User::factory(),
            'folder_id' => \App\Models\Folder::factory(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        // Juste après la création d'un Document...
        return $this->afterCreating(function (Document $document) {

            // ... on lui crée entre 1 et 3 attachments
            Attachment::factory(rand(1, 3))->create([
                'document_id' => $document->id,
            ]);
        });
    }
}
