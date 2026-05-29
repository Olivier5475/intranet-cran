<?php

namespace App\Http\Controllers;

use App\Exception\ServerException;
use App\Services\Interfaces\DocumentsServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;
use Inertia\Inertia;
use Inertia\Response;

class MainController extends Controller {

    public function __invoke(DocumentsServiceInterface $documentsService): Response
    {
        return Inertia::render('Home', [
            "folder_id" => 0,
        ]);
    }
}
