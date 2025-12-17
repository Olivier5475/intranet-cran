<?php

namespace App\Services\Interfaces;

interface DecodageServiceInterface {
    public function decode_utf8_recursive($data) ;
    public function getSanitizedContentAttribute($content);
}
