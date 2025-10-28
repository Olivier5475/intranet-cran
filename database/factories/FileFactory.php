<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    public function definition(): array
    {
        // On simule des types de fichiers courants
        $mimetypes = [
            'jpg' => 'image/jpeg',
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'mp4' => 'video/mp4',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
        ];
        $ext = array_rand($mimetypes);
        $name = fake()->words(2, true);

        return [
            'name' => $name . '.' . $ext,
            'user_id' => \App\Models\User::factory(), // Sera écrasé par le Seeder
            'folder_id' => \App\Models\Folder::factory(), // Sera écrasé par le Seeder
            'storage_path' => 'user_files/' . fake()->uuid() . '.' . $ext,
            'mimetype' => $mimetypes[$ext],
            'size' => fake()->numberBetween(1024, 10000000), // de 1KB à 10MB
        ];
    }
}
