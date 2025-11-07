<?php

namespace App\Exception;

class DiskWriteException extends \Exception {
    public function __construct(string $message = "Erreur lors de l'écriture du fichier sur le disque", int $code = 500, ?\Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

