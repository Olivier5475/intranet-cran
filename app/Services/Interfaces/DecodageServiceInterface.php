<?php

namespace App\Services\Interfaces;

interface DecodageServiceInterface {

    /**
     * Parcourt récursivement un tableau ou une chaîne pour corriger l'encodage UTF-8.
     * * @param mixed $data
     * @return mixed
     */
    public function decode_utf8_recursive(mixed $data): mixed;

    /**
     * Nettoie le contenu HTML pour prévenir les failles XSS tout en préservant le formatage.
     * * @param string|null $content
     * @return string
     */
    public function getSanitizedContentAttribute(?string $content): string;
}
