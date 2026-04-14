<?php

namespace App\Services\Interfaces;

use App\DTO\DocumentDTO;
use App\Exception\{DocumentNotFoundException, ServerException};
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

interface DocumentsServiceInterface
{
    /**
     * Récupère un document par son identifiant.
     *
     * @param int $id
     * @return DocumentDTO
     * @throws DocumentNotFoundException Si le document n'existe pas.
     */
    public function read(int $id): DocumentDTO;

    /**
     * Récupère le document spécial de la racine (page d'accueil/Wiki).
     *
     * @return DocumentDTO|null
     * @throws ServerException En cas d'erreur lors de la récupération.
     */
    public function readRacineDoc(): ?DocumentDTO;

    /**
     * Vérifie si l'utilisateur actuel a le droit de modifier le document.
     * Basé sur le rôle admin ou l'intersection des départements.
     *
     * @param int $document_id
     * @return bool
     */
    public function hasEditAccess(int $document_id): bool;

    /**
     * Récupère l'ID du dossier parent d'un document.
     *
     * @param int $document_id
     * @return int
     */
    public function getParentId(int $document_id): int;

    /**
     * Crée un document et ses attachements associés.
     *
     * @param array{name: string, content: string, folder_id: int, new_attachments?: array} $data
     * @return DocumentDTO
     * @throws BadRequestException Si les champs obligatoires sont manquants.
     * @throws \Throwable En cas d'échec de la transaction.
     */
    public function create(array $data): DocumentDTO;

    /**
     * Met à jour un document, gère la synchronisation des fichiers (ajout, suppression, renommage).
     *
     * @param int $id
     * @param array $data
     * @return DocumentDTO
     * @throws BadRequestException Si l'ID est invalide ou si un fichier n'appartient pas au document.
     * @throws \Throwable
     */
    public function update(int $id, array $data): DocumentDTO;

    /**
     * Archive (suppression logique) un document.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restaure un document archivé.
     *
     * @param int $document_id
     * @return bool
     */
    public function restore(int $document_id): bool;
}
