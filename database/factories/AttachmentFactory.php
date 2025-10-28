<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        // (Même logique que FileFactory pour les mimetypes)
        $mimetypes = [
            'jpg' => 'image/jpeg',
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $ext = array_rand($mimetypes);
        $name = fake()->word();

        return [
            'document_id' => \App\Models\Document::factory(), // Sera lié dans le Seeder
            'name' => $name . '.' . $ext,
            'storage_path' => 'attachments/' . fake()->uuid() . '.' . $ext,
            'mimetype' => $mimetypes[$ext],
            'size' => fake()->numberBetween(1024, 5000000),
        ];
    }
}
