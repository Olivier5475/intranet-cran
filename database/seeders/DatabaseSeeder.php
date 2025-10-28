<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crée un utilisateur de test principal
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'), // mdp: password
        ]);

        // 2. Crée 3 Dossiers Racines pour cet utilisateur
        $rootFolders = Folder::factory(3)->create([
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        // 3. On prend le premier dossier racine (ex: "Mes Documents")
        $firstFolder = $rootFolders->first();

        // 4. On crée 5 fichiers simples dedans
        File::factory(5)->create([
            'user_id' => $user->id,
            'folder_id' => $firstFolder->id,
        ]);

        // 5. On crée 3 "Documents" (avec leurs attachments) dedans
        // Les attachments sont créés automatiquement (voir DocumentFactory)
        Document::factory(3)->create([
            'user_id' => $user->id,
            'folder_id' => $firstFolder->id,
        ]);

        // 6. On crée 2 sous-dossiers dans "Mes Documents"
        $subFolders = Folder::factory(2)->create([
            'user_id' => $user->id,
            'parent_id' => $firstFolder->id,
        ]);

        // 7. On met 2-3 fichiers dans le premier sous-dossier
        File::factory(3)->create([
            'user_id' => $user->id,
            'folder_id' => $subFolders->first()->id,
        ]);

        // 8. On prend le deuxième dossier racine (ex: "Projets")
        $secondFolder = $rootFolders->get(1);
        Document::factory(2)->create([
            'user_id' => $user->id,
            'folder_id' => $secondFolder->id,
        ]);
    }
}
