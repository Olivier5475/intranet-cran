<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers as Controllers;

Route::get('/', Controllers\MainController::class)->name('home');
Route::get('/navigation/{folder_id}', Controllers\NavigationController::class)->name('navigation');
Route::get('/flashTest', Controllers\TestFlashController::class)->name('flash');
Route::get("/documents/{id}", Controllers\DocumentViewController::class)->name('document');
