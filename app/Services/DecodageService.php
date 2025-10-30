<?php

namespace App\Services;

use App\Services\Interface\DecodageServiceInterface;

class DecodageService implements DecodageServiceInterface {
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
}
