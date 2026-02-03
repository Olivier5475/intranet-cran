<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Middleware;

Route::get('/', Controllers\MainController::class)->name('home');

Route::get("/download/attachment/{id}", [Controllers\DownloadController::class, "attachment"])->name('download.attachment');
Route::get("/download/file/{id}", [Controllers\DownloadController::class, "file"])->name('download.file');

Route::prefix('/navigation')->group(function () {
    Route::get('/f/{folder_id}', Controllers\NavigationController::class)->name('navigate.folder');
    Route::get("/d/{document_id}", Controllers\DocumentViewController::class)->name('navigate.document');
});

Route::prefix("editor")
    ->middleware(Middleware\IsEditeur::class)
    ->group(function () {
        Route::prefix("folders")->group(function () {
            // GET (form)
            Route::get("create/p/{parent_id}", [Controllers\Admin\FolderController::class, "create"])->name("editor.folder.create");
            Route::get("/update/{folder_id}", [Controllers\Admin\FolderController::class, "update"])->name("editor.folder.update");

            // POST (submit)
            Route::patch("/store/{folder_id}", [Controllers\Admin\FolderController::class, "store"])->name("editor.folder.post.update");
            Route::post("/store", [Controllers\Admin\FolderController::class, "store"])->name("editor.folder.post.create");
        });

        Route::prefix("documents")->group(function () {
            // GET (form)
            Route::get("/create/p/{parent_id}", [Controllers\Admin\DocumentController::class, "create"])->name('editor.document.create');
            Route::get("/update/{document_id}", [Controllers\Admin\DocumentController::class, "update"])->name('editor.document.update');

            // POST (submit)
            Route::post("/store", [Controllers\Admin\DocumentController::class, "store"])->name('editor.document.post.create');
            Route::post("/store/{document_id}", [Controllers\Admin\DocumentController::class, "store"])->name('editor.document.post.update');

            // DELETE
            Route::delete("/delete/{document_id}", [Controllers\Admin\DocumentController::class, "delete"])->name('editor.document.delete');
        });

        Route::prefix("files")->group(function () {
            // GET (form)
            Route::get("/create/p/{parent_id}", [Controllers\Admin\FileController::class, "create"])->name('editor.file.create');
            Route::get("/update/{file_id}", [Controllers\Admin\FileController::class, "update"])->name('editor.file.update');

            // POST (submit)
            Route::post("/store", [Controllers\Admin\FileController::class, "store"])->name('editor.file.post.create');
            Route::post("/store/{file_id}", [Controllers\Admin\FileController::class, "store"])->name('editor.file.post.update');

            // DELETE
            Route::delete("/delete/{file_id}", [Controllers\Admin\FileController::class, "delete"])->name('editor.file.delete');
        });
    });

Route::prefix("admin")
    ->middleware(Middleware\IsAdmin::class)
    ->group(function () {
    Route::prefix("users")->group(function () {
        // GET
        Route::get("", [Controllers\Admin\UsersController::class, "readAll"])->name("admin.user");
        Route::get("/create", [Controllers\Admin\UsersController::class, "create"])->name("admin.user.create");
        Route::get("/{id}", [Controllers\Admin\UsersController::class, "update"])->name("admin.user.update");

        // POST
        Route::post("/", [Controllers\Admin\UsersController::class, "store"])->name("admin.user.post.create");

        // PATCH
        Route::patch("/{id}", [Controllers\Admin\UsersController::class, "store"])->name("admin.user.post.update");

        // DELETE
        Route::delete("/{id}", [Controllers\Admin\UsersController::class, "delete"])->name("admin.user.delete");
    });

    Route::prefix("departements")->group(function () {
        // GET
        Route::get("", [Controllers\Admin\DepartementController::class, "readAll"])->name("admin.departements");
        Route::get("/create", [Controllers\Admin\DepartementController::class, "create"])->name("admin.departements.create");
        Route::get("/{id}", [Controllers\Admin\DepartementController::class, "update"])->name("admin.departements.update");

        // POST
        Route::post("/", [Controllers\Admin\DepartementController::class, "store"])->name("admin.departements.post.create");

        // PATCH
        Route::patch("/{id}", [Controllers\Admin\DepartementController::class, "store"])->name("admin.departements.post.update");

        // DELETE
        Route::delete("/{id}", [Controllers\Admin\DepartementController::class, "delete"])->name("admin.departements.delete");
    });
});

