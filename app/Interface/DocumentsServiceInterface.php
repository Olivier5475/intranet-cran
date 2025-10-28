<?php

namespace App\Interface;
use App\DTO\DocumentViewDTO;

interface DocumentsServiceInterface {

    public function getDocumentView($id) : DocumentViewDTO;
}
