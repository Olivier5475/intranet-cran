<?php

namespace App\Services\Interfaces;

interface DecodageServiceInterface
{
    /**
     * Parcourt récursivement un tableau ou une chaîne pour convertir l'encodage en UTF-8.
     * Utile pour traiter des données provenant de sources legacy (ISO-8859-1).
     *
     * @param mixed $data Les données à décoder (string, array, ou autre).
     * @return mixed Les données converties en UTF-8.
     */
    public function decode_utf8_recursive(mixed $data): mixed;

    /**
     * Nettoie une chaîne de caractères HTML pour prévenir les failles XSS.
     * Utilise HTMLPurifier pour supprimer les scripts et attributs dangereux.
     *
     * @param string|null $content Le contenu HTML brut à nettoyer.
     * @return string Le contenu nettoyé et sécurisé.
     */
    public function getSanitizedContentAttribute(?string $content): string;
}
