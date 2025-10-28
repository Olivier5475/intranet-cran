<?php

namespace App\Providers;

use App\Interface\DocumentsServiceInterface;
use App\Interface\FoldersServiceInterface;
use App\Services\DocumentService;
use App\Services\FoldersService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            FoldersServiceInterface::class,
            FoldersService::class
        );

        $this->app->bind(
            DocumentsServiceInterface::class,
            DocumentService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
