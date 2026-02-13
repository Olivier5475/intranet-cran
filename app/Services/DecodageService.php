<?php

namespace App\Services;

use App\Services\Interfaces\DecodageServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class DecodageService implements DecodageServiceInterface {

    /**
     * @inheritDoc
     */
    public function decode_utf8_recursive(mixed $data): mixed {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->decode_utf8_recursive($value);
            }
        } elseif (is_string($data)) {
            // On tente de détecter si la chaîne est déjà en UTF-8 pour éviter le double encodage
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            }
            return $data;
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getSanitizedContentAttribute(?string $content): string {
        if (empty($content)) {
            return '';
        }

        try {
            // Utilisation de strip_tags pour un nettoyage basique ou HTMLPurifier si installé
            // Ici, on protège contre les injections de scripts tout en gardant le texte
            return clean($content); // Si tu utilises un package comme mews/purifier
            // Sinon par défaut :
            // return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        } catch (Throwable $t) {
            Log::error("Échec du nettoyage de contenu (XSS Prevention)", [
                'error' => $t->getMessage(),
                'content_preview' => substr($content, 0, 100)
            ]);
            return ""; // On retourne vide par sécurité en cas d'échec du cleaner
        }
    }
}
