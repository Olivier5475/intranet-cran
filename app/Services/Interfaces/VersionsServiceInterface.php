<?php

namespace App\Services\Interfaces;

use App\DTO\VersionDTO;
use App\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface VersionsServiceInterface
{
    /**
     * Récupère l'historique des versions pour une entité parente donnée.
     *
     * @param int $parent_id Identifiant de l'entité (File ou Document).
     * @param string $modelString Type d'entité ('files' ou 'documents').
     * @return array<int, VersionDTO> Liste des versions formatées en DTO.
     * @throws BadRequestException Si le type d'entité n'est pas supporté.
     */
    public function readVersionsFromParent(int $parent_id, string $modelString): array;

    /**
     * Restaure une entité à partir d'un état historique (Version).
     * Gère la restauration des attributs, des fichiers physiques et des relations (départements, attachements).
     *
     * @param int $versionId Identifiant de la version à restaurer.
     * @param string $modelString Type d'entité cible ('files' ou 'documents').
     * @return void
     * @throws BadRequestException Si le modèle est invalide.
     * @throws FileNotFoundException Si le fichier archivé est introuvable sur le disque.
     * @throws \Exception Si la version ne correspond pas au type d'entité demandé.
     * @throws \Throwable En cas d'échec de la transaction de restauration.
     */
    public function restoreFromVersionId(int $versionId, string $modelString): void;
}
