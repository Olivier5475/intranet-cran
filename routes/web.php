<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Middleware;

Route::get('/', Controllers\MainController::class)->name('home');

Route::get("/download/attachment/{id}", [Controllers\DownloadController::class, "attachment"])->name('download.attachment');
Route::get("/download/file/{id}", [Controllers\DownloadController::class, "file"])->name('download.file');

Route::prefix('/navigation/{folder_id}')->group(function () {
    Route::get('/', Controllers\NavigationController::class)->name('navigation');
    Route::get("/documents/{id}", Controllers\DocumentViewController::class)->name('document');

    Route::prefix("admin")
        ->middleware(Middleware\IsEditeur::class)
        ->group(function () {
        Route::prefix("folders")->group(function () {
            // GET (form)
            Route::get("create", [Controllers\Admin\FolderController::class, "create"])->name("folder.create");
            Route::get("/update/{id}", [Controllers\Admin\FolderController::class, "update"])->name("folder.update");

            // POST (submit)
            Route::patch("/store/{id}", [Controllers\Admin\FolderController::class, "store"])->name("folder.post.update");
            Route::post("/store", [Controllers\Admin\FolderController::class, "store"])->name("folder.post.create");
        });

        Route::prefix("documents")->group(function () {
            // GET (form)
            Route::get("/create", [Controllers\Admin\DocumentController::class, "create"])->name('document.create');
            Route::get("/update/{id}", [Controllers\Admin\DocumentController::class, "update"])->name('document.update');

            // POST (submit)
            Route::post("/store", [Controllers\Admin\DocumentController::class, "store"])->name('document.post.create');
            Route::post("/store/{id}", [Controllers\Admin\DocumentController::class, "store"])->name('document.post.update');

            // DELETE
            Route::delete("/delete/{id}", [Controllers\Admin\DocumentController::class, "delete"])->name('document.post.delete');
        });

        Route::prefix("files")->group(function () {
            // GET (form)
            Route::get("/create", [Controllers\Admin\FileController::class, "create"])->name('document.create');
            Route::get("/update/{id}", [Controllers\Admin\FileController::class, "update"])->name('document.update');

            // POST (submit)
            Route::post("/store", [Controllers\Admin\FileController::class, "store"])->name('file.post.create');
            Route::post("/store/{id}", [Controllers\Admin\FileController::class, "store"])->name('file.post.update');

            // DELETE
            Route::delete("/delete/{id}", [Controllers\Admin\FileController::class, "delete"])->name('document.post.delete');
        });
    });
});

Route::prefix("admin")
    ->middleware(Middleware\IsAdmin::class)
    ->group(function () {
    Route::prefix("users")->group(function () {
        // GET
        Route::get("", [Controllers\Admin\UsersController::class, "readAll"])->name("admin.users");
        Route::get("/create", [Controllers\Admin\UsersController::class, "create"])->name("admin.user.create");
        Route::get("/{id}", [Controllers\Admin\UsersController::class, "update"])->name("admin.user.update");

        // POST
        Route::post("/", [Controllers\Admin\UsersController::class, "store"])->name("admin.user.post.update");

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

