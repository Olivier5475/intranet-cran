<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Middleware;

Route::get('/', Controllers\MainController::class)->name('home');

Route::get("/download/attachment/{id}", [Controllers\DownloadController::class, "attachment"])->name('download.attachment');
Route::get("/download/file/{id}", [Controllers\DownloadController::class, "file"])->name('download.file');
Route::get("/download/file/version/{id}", [Controllers\DownloadController::class, "version"])->name('download.file.version');

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
            Route::get("create/p/{parent_id}", [Controllers\Admin\FolderController::class, "create"])->name("editor.folder.create");
            Route::get("/update/{folder_id}", [Controllers\Admin\FolderController::class, "update"])->name("editor.folder.update");

            // POST (submit)
            Route::post("/store", [Controllers\Admin\FolderController::class, "store"])->name("editor.folder.post.create");

            // PATCH (submit)
            Route::patch("/store/{folder_id}", [Controllers\Admin\FolderController::class, "store"])->name("editor.folder.post.update");
            Route::patch("/restore/{folder_id}", [Controllers\Admin\FolderController::class, "restore"])->name("editor.folder.post.restore");

            // DELETE
            Route ::delete("/delete/{folder_id}", [Controllers\Admin\FolderController::class, "delete"])->name("editor.folder.delete");
        });

        Route::prefix("documents")->group(function () {
            // GET (form)
            Route::get("/create/p/{parent_id}", [Controllers\Admin\DocumentController::class, "create"])->name('editor.document.create');
            Route::get("/update/{document_id}", [Controllers\Admin\DocumentController::class, "update"])->name('editor.document.update');

            // POST (submit)
            Route::post("/store", [Controllers\Admin\DocumentController::class, "store"])->name('editor.document.post.create');
            Route::post("/store/{document_id}", [Controllers\Admin\DocumentController::class, "store"])->name('editor.document.post.update');

            // PATCH (submit)
            Route::patch("/restore/{document_id}", [Controllers\Admin\DocumentController::class, "restore"])->name("editor.document.post.restore");

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

            // PATCH (submit)
            Route::patch("/restore/{file_id}", [Controllers\Admin\FileController::class, "restore"])->name("editor.file.post.restore");

            // DELETE
            Route::delete("/delete/{file_id}", [Controllers\Admin\FileController::class, "delete"])->name('editor.file.delete');
        });

        Route::get("{model}/history/{model_id}", [Controllers\Admin\VersionController::class, "history"])->name('editor.model.history');
        Route::post("{model}/restore/{version_id}", [Controllers\Admin\VersionController::class, "restore"])->name('editor.model.post.restore');
    });

Route::prefix("admin")
    ->middleware(Middleware\IsAdmin::class)
    ->group(function () {
    Route::prefix("users")->group(function () {
        // GET
        Route::get("", [Controllers\Admin\UsersController::class, "readAll"])->name("admin.user");

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
        Route::get("/{id}/users", [Controllers\Admin\DepartementController::class, "users"])->name("admin.departements.users");

        // POST
        Route::post("/", [Controllers\Admin\DepartementController::class, "store"])->name("admin.departements.post.create");

        // PATCH
        Route::patch("/{id}", [Controllers\Admin\DepartementController::class, "store"])->name("admin.departements.post.update");

        // DELETE
        Route::delete("/{id}", [Controllers\Admin\DepartementController::class, "delete"])->name("admin.departements.delete");
        Route::delete("/{id}/user/{user_id}", [Controllers\Admin\DepartementController::class, "removeUser"])->name("admin.departements.users.remove");
    });
});

