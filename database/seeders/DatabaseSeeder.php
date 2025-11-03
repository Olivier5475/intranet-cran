<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Document;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Ajouté pour le nettoyage

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        Storage::deleteDirectory('public/documents');
        Storage::deleteDirectory('public/files');
        Storage::makeDirectory('public/documents');
        Storage::makeDirectory('public/files');

        Departement::create([
            'name' => 'Contrôle Identification Diagnostic ',
            'initials' => 'CID',
        ]);
        Departement::create([
            'name' => 'Biologie, Signaux et Systèmes en Cancérologie et Neurosciences ',
            'initials' => 'BioSiS',
        ]);

        Departement::create([
            'name' => 'Modélisation Pilotage des Systèmes Industriels ',
            'initials' => 'MPSI',
        ]);


        // --- 1. Création des utilisateurs principaux ---
        $mainUser1 = User::factory()->create([
            'nom' => 'Test1',
            'prenom' => 'User1',
            'email' => 'test@univ-lorraine.fr',
            "role" => "admin",
        ]);

        $mainUser2 = User::factory()->create([
            'nom' => 'Test2',
            'prenom' => 'User2',
            'email' => 'test2@univ-lorraine.fr',
            "role" => "user",
        ]);

        $mainUsers = collect([$mainUser1, $mainUser2]);

        // --- 2. Boucle pour peupler le compte de chaque utilisateur principal ---
        foreach ($mainUsers as $user) {

            // 2.1. Crée 3 Dossiers Racines pour cet utilisateur
            $rootFolders = Folder::factory(3)->create([
                'user_id' => $user->id,
                'parent_id' => null, // Racine
            ]);

            // 2.2. On peuple le premier dossier racine (ex: "Mes Documents")
            $firstFolder = $rootFolders->first();

            // 5 fichiers simples
            File::factory(5)->create([
                'user_id' => $user->id,
                'folder_id' => $firstFolder->id,
            ]);

            // 3 "Documents" (avec leurs attachments)
            Document::factory(3)->create([
                'user_id' => $user->id,
                'folder_id' => $firstFolder->id,
            ]);

            // 2 sous-dossiers dans "Mes Documents"
            $subFolders = Folder::factory(2)->create([
                'user_id' => $user->id,
                'parent_id' => $firstFolder->id,
            ]);

            // On peuple le *premier* sous-dossier
            File::factory(3)->create([
                'user_id' => $user->id,
                'folder_id' => $subFolders->first()->id,
            ]);

            // On peuple le *deuxième* sous-dossier
            Document::factory(2)->create([
                'user_id' => $user->id,
                'folder_id' => $subFolders->last()->id,
            ]);


            // 2.3. On peuple le deuxième dossier racine (ex: "Projets")
            $secondFolder = $rootFolders->get(1);

            Document::factory(2)->create([
                'user_id' => $user->id,
                'folder_id' => $secondFolder->id,
            ]);

            // On ajoute un sous-dossier dans "Projets" pour plus de profondeur
            $projectSubFolder = Folder::factory()->create([
                'user_id' => $user->id,
                'parent_id' => $secondFolder->id,
            ]);

            File::factory(4)->create([
                'user_id' => $user->id,
                'folder_id' => $projectSubFolder->id,
            ]);


            // 2.4. On peuple le troisième dossier racine (ex: "Archives")
            $thirdFolder = $rootFolders->get(2);

            File::factory(2)->create([
                'user_id' => $user->id,
                'folder_id' => $thirdFolder->id,
            ]);
            Document::factory(1)->create([
                'user_id' => $user->id,
                'folder_id' => $thirdFolder->id,
            ]);
        }

        $departements = Departement::pluck('id')->toArray();
        $files = File::all();
        $documents = Document::all();

        foreach ($files as $file) {
            // Chaque fichier est lié à entre 1 et 3 départements aléatoires
            $file->departements()->sync(fake()->randomElements($departements, rand(0, 3)));
        }

        foreach ($documents as $document) {
            // Chaque document est lié à entre 1 et 3 départements aléatoires
            $document->departements()->sync(fake()->randomElements($departements, rand(0, 3)));
        }


        // --- 3. Création d'utilisateurs supplémentaires (sans fichiers) ---
        // Utile pour tester l'affichage d'un compte vide, la pagination, etc.
        User::factory(10)->create([
            "role" => "user",
        ]);
    }
}
