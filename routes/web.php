<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Controllers;

Route::get('/', Controllers\MainController::class)->name('home');

Route::get("/download/attachment/{id}", [Controllers\DownloadController::class, "attachment"])->name('download.attachment');
Route::get("/download/file/{id}", [Controllers\DownloadController::class, "file"])->name('download.file');

Route::prefix('/navigation/{folder_id}')->group(function () {
    Route::get('/', Controllers\NavigationController::class)->name('navigation');

    Route::get("/documents/{id}", Controllers\DocumentViewController::class)->name('document');

    Route::prefix("admin")->group(function () {
        Route::prefix("folders")->group(function () {
            // GET (form)
            Route::get("create", [Controllers\Admin\FolderController::class, "create"])->name("folder.create");
            Route::get("/update/{id}", [Controllers\Admin\FolderController::class, "update"])->name("folder.create");

            // POST (submit)
            Route::patch("/store/{id}", [Controllers\Admin\FolderController::class, "store"])->name("folder.store");
            Route::post("/store", [Controllers\Admin\FolderController::class, "store"])->name("folder.store");
        });

        Route::prefix("documents")->group(function () {
            // GET (form)
            Route::get("/create", [Controllers\Admin\DocumentController::class, "create"])->name('document.create');
            Route::get("/update/{id}", [Controllers\Admin\DocumentController::class, "update"])->name('document.update');

            // POST (submit)
            Route::patch("/store/{id}", [Controllers\Admin\DocumentController::class, "store"])->name('document.post.update');
            Route::post("/store", [Controllers\Admin\DocumentController::class, "store"])->name('document.post.create');

            // DELETE
            Route::delete("/delete/{id}", [Controllers\Admin\DocumentController::class, "delete"])->name('document.post.delete');
        });

        Route::prefix("files")->group(function () {
            // GET (form)
            Route::get("/create", [Controllers\Admin\FileController::class, "create"])->name('document.create');
            Route::get("/update/{id}", [Controllers\Admin\FileController::class, "update"])->name('document.update');

            // POST (submit)
            Route::patch("/store/{id}", [Controllers\Admin\FileController::class, "store"])->name('file.post.update');
            Route::post("/store", [Controllers\Admin\FileController::class, "store"])->name('file.post.create');

            // DELETE
            Route::delete("/delete/{id}", [Controllers\Admin\FileController::class, "delete"])->name('document.post.delete');
        });
    });
});


