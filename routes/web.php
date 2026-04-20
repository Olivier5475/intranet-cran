<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Middleware;

Route::get('/', Controllers\MainController::class)->name('home');

Route::get("/download/attachment/{id}", [Controllers\DownloadController::class, "attachment"])->name('download.attachment');
Route::get("/download/file/{id}", [Controllers\DownloadController::class, "file"])->name('download.file');
Route::get("/download/file/version/{id}", [Controllers\DownloadController::class, "version"])->name('download.file.version');

Route::get("/preview/file/{id}", [Controllers\DownloadController::class, "preview"])->name('download.file.preview');

Route::prefix('/navigation')->group(function () {
    Route::get('/f/{folder_id}', Controllers\NavigationController::class)->name('navigate.folder');
    Route::get('/f/{folder_id}/archived', Controllers\NavigationController::class)->name('navigate.archived');
    Route::get("/d/{document_id}", Controllers\DocumentViewController::class)->name('navigate.document');
});

Route::get('/logout', [Controllers\AuthController::class, 'logout'])->name('logout');

Route::prefix("editor")
    ->middleware(Middleware\IsEditeur::class)
    ->group(function () {
        Route::prefix("folders")->group(function () {
            // GET (form)
            Route::get("/create/p/{parent_id}", [Controllers\Admin\FolderController::class, "create"])->name("editor.folder.create");
            Route::get("/update/{folder_id}", [Controllers\Admin\FolderController::class, "edit"])->name("editor.folder.update");

            // POST (submit)
            Route::post("/", [Controllers\Admin\FolderController::class, "store"])->name("editor.folder.post.create");
            Route::post("/{folder_id}", [Controllers\Admin\FolderController::class, "update"])->name("editor.folder.post.update");

            // PATCH (submit)
            Route::patch("/restore/{folder_id}", [Controllers\Admin\FolderController::class, "restore"])->name("editor.folder.post.restore");

            // DELETE
            Route::delete("/{folder_id}", [Controllers\Admin\FolderController::class, "archive"])->name("editor.folder.archive");
        });

        Route::prefix("documents")->group(function () {
            // GET (form)
            Route::get("/create/p/{parent_id}", [Controllers\Admin\DocumentController::class, "create"])->name('editor.document.create');
            Route::get("/update/{document_id}", [Controllers\Admin\DocumentController::class, "edit"])->name('editor.document.update');

            // POST (submit)
            Route::post("/", [Controllers\Admin\DocumentController::class, "store"])->name('editor.document.post.create');
            Route::post("/{document_id}", [Controllers\Admin\DocumentController::class, "update"])->name('editor.document.post.update');

            // PATCH (submit)
            Route::patch("/restore/{document_id}", [Controllers\Admin\DocumentController::class, "restore"])->name("editor.document.post.restore");

            // DELETE
            Route::delete("/{document_id}", [Controllers\Admin\DocumentController::class, "archive"])->name('editor.document.archive');
        });

        Route::prefix("files")->group(function () {
            // GET (form)
            Route::get("/create/p/{parent_id}", [Controllers\Admin\FileController::class, "create"])->name('editor.file.create');
            Route::get("/update/{file_id}", [Controllers\Admin\FileController::class, "edit"])->name('editor.file.update');

            // POST (submit)
            Route::post("/", [Controllers\Admin\FileController::class, "store"])->name('editor.file.post.create');
            Route::post("/{file_id}", [Controllers\Admin\FileController::class, "update"])->name('editor.file.post.update');

            // PATCH (submit)
            Route::patch("/restore/{file_id}", [Controllers\Admin\FileController::class, "restore"])->name("editor.file.post.restore");

            // DELETE
            Route::delete("/{file_id}", [Controllers\Admin\FileController::class, "archive"])->name('editor.file.archive');
        });

        Route::get("/{model}/history/{model_id}", [Controllers\Admin\VersionController::class, "history"])->name('editor.model.history');
        Route::post("/{model}/restore/{version_id}", [Controllers\Admin\VersionController::class, "restore"])->name('editor.model.post.restore');
    });

Route::prefix("admin")
    ->middleware(Middleware\IsAdmin::class)
    ->group(function () {
        // SUPPRESSION DEFINITIVE
        Route::delete("/folders/{folder_id}", [Controllers\Admin\FolderController::class, "delete"])->name("admin.folder.delete");
        Route::delete("/documents/{document_id}", [Controllers\Admin\DocumentController::class, "delete"])->name("admin.document.delete");
        Route::delete("/files/{document_id}", [Controllers\Admin\FileController::class, "delete"])->name("admin.file.delete");

        Route::prefix("users")->group(function () {
            // GET
            Route::get("/", [Controllers\Admin\UsersController::class, "index"])->name("admin.user");

            // POST
            Route::post("/", [Controllers\Admin\UsersController::class, "store"])->name("admin.user.post.create");

            // PATCH
            Route::patch("/{id}", [Controllers\Admin\UsersController::class, "update"])->name("admin.user.post.update");

            // DELETE
            Route::delete("/{id}", [Controllers\Admin\UsersController::class, "delete"])->name("admin.user.delete");
        });

        Route::prefix("departements")->group(function () {
            // GET
            Route::get("/", [Controllers\Admin\DepartementController::class, "index"])->name("admin.departements");
            Route::get("/{id}/users", [Controllers\Admin\DepartementController::class, "users"])->name("admin.departements.users");

            // POST
            Route::post("/", [Controllers\Admin\DepartementController::class, "store"])->name("admin.departements.post.create");

            // PATCH
            Route::patch("/{id}", [Controllers\Admin\DepartementController::class, "update"])->name("admin.departements.post.update");

            // DELETE
            Route::delete("/{id}", [Controllers\Admin\DepartementController::class, "delete"])->name("admin.departements.delete");
            Route::delete("/{id}/user/{user_id}", [Controllers\Admin\DepartementController::class, "removeUser"])->name("admin.departements.users.remove");
        });
});

