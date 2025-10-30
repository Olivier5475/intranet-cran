<?php

namespace App\Services\Interface;
use App\DTO\DocumentViewDTO;

interface DocumentsServiceInterface {

    public function getDocumentView($id) : DocumentViewDTO;
}
