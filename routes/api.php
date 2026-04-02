<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('/documents')->group(function () {
    Route::post('/upload-image', [Controllers\Api\DocumentController::class, 'upload'])->name('document.upload-image');
});
