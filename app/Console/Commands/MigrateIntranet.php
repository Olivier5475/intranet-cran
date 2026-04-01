<?php

namespace App\Console\Commands;

use App\Models\Folder;
use Illuminate\Console\Command;
use App\Services\Interfaces\FilesServiceInterface;
use App\Services\Interfaces\FoldersServiceInterface;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Illuminate\Http\File;

class MigrateIntranet extends Command
{
    protected $signature = 'intranet:migrate {path} {departement?}';
    protected $description = 'Migre récursivement un dossier local vers la nouvelle structure';

    public function __construct(
        private readonly FilesServiceInterface $filesService,
        private readonly FoldersServiceInterface $foldersService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(0);

        Folder::unsetEventDispatcher();
        \App\Models\File::unsetEventDispatcher();

        $rootPath = $this->argument('path');
        $deptId = (int) ($this->argument('departement') ?? 1);
        $adminId = 1;

        if (!is_dir($rootPath)) {
            $this->error("Dossier introuvable.");
            return 1;
        }

        $directory = new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);
        $folderMap = [dirname($rootPath) => 0];

        foreach ($iterator as $item) {
            $currentPath = $item->getRealPath();
            $parentPath = dirname($currentPath);
            $parentId = $folderMap[$parentPath] ?? 0;

            try {
                if ($item->isDir()) {
                    $fold_data = [
                        'name' => $item->getFilename(),
                        'color' => '#9553E9',
                        'user_id' => $adminId,
                        'departements' => [$deptId]
                    ];
                    if(!empty($parentId)) {
                        $fold_data['parent_id'] = $parentId;
                    }
                    $folderDTO = $this->foldersService->create($fold_data);
                    $folderMap[$currentPath] = $folderDTO->id;
                    $this->info("Dossier créé : " . $item->getFilename());
                } else {
                    try {
                        $fileObject = new \Illuminate\Http\File($currentPath);
                        // Vérifie si le fichier est lisible
                        if (!$item->isReadable()) {
                            $this->error("Fichier non lisible : " . $currentPath);
                            continue;
                        }
                        $this->filesService->create([
                            'folder_id' => $parentId,
                            'file' => $fileObject,
                            'name' => $item->getFilename(),
                            'user_id' => $adminId,
                            'departements' => [$deptId]
                        ]);
                        $this->info("  Fichier migré : " . $item->getFilename());
                    } catch (\Throwable $e) {
                        // On catche Throwable pour être sûr de voir les erreurs de type (TypeError)
                        $this->error("  Échec fichier " . $item->getFilename() . " : " . $e->getMessage());
                        Log::error("Erreur migration fichier", ['file' => $item->getFilename(), 'error' => $e->getMessage()]);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Erreur sur " . $item->getFilename() . " : " . $e->getMessage());
            }
        }
        return 0;
    }
}
