<?php

namespace App\Services\Interfaces;

use App\Exception\FileNotFoundException;
use App\Exception\PersistenceException;
use Exception;
use Throwable;

interface VersionsServiceInterface
{
    /**
     * Restaure les attributs et le fichier physique d'une version donnée.
     * @param int $versionId
     * @param string $modelString
     * @return void
     * @throws PersistenceException|FileNotFoundException|Exception
     * @throws Throwable
     */
    public function restoreFromVersionId(int $versionId, string $modelString): void ;

    /**
     * @param int $parent_id
     * @param string $modelString
     * @return array
     */
    public function readVersionsFromParent(int $parent_id, string $modelString): array ;
}
