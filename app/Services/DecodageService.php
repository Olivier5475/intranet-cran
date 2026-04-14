<?php

namespace App\Services;

use App\Services\Interfaces\DecodageServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class DecodageService implements DecodageServiceInterface
{
    // --- ENCODAGE & FORMATAGE ---

    /**
     * @inheritDoc
     */
    public function decode_utf8_recursive(mixed $data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->decode_utf8_recursive($value);
            }
            return $data;
        }

        if (is_string($data)) {
            // On vérifie si la chaîne est déjà en UTF-8 pour éviter de corrompre les caractères
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            }
        }

        return $data;
    }

    // --- SÉCURISATION & NETTOYAGE ---

    /**
     * @inheritDoc
     */
    public function getSanitizedContentAttribute(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        try {
            // Nettoyage via HTMLPurifier (configuré dans config/purifier.php)
            return clean($content);
        } catch (Throwable $t) {
            Log::error("Échec du nettoyage de contenu (XSS Prevention)", [
                'error' => $t->getMessage(),
                'content_preview' => substr($content, 0, 100)
            ]);

            // En cas d'erreur critique du moteur de nettoyage, on retourne une chaîne vide
            // pour garantir qu'aucun contenu non filtré n'est affiché.
            return "";
        }
    }
}
