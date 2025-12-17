<?php

namespace App\Services;
use HTMLPurifier;
use HTMLPurifier_Config;

class DecodageService implements Interfaces\DecodageServiceInterface {
    public function __construct(public $url = "/fake-external-users") {
        $this->url = url($this->url);
    }

    public function decode_utf8_recursive($data) {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'ISO-8859-1', 'UTF-8');
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->decode_utf8_recursive($value);
            }
        }
        return $data;
    }

    public function getSanitizedContentAttribute($content){
        $config = HTMLPurifier_Config::createDefault();
        // Configurez ici les balises et attributs que vous autorisez.
        $purifier = new HTMLPurifier($config);

        // Nettoyer le contenu brut avant de l'afficher
        return $purifier->purify($content);
    }
}
