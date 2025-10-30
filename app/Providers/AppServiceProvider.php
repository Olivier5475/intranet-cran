<?php

namespace App\Providers;

use App\repositories\FolderRepository;
use App\repositories\interfaces\FolderRepositoryInterface;
use App\repositories\interfaces\UserRepositoryInterface;
use App\repositories\UserRepository;
use App\Services\AuthService;
use App\Services\DecodageService;
use App\Services\DepartementsService;
use App\Services\DocumentService;
use App\Services\FoldersService;
use App\Services\Interface\DecodageServiceInterface;
use App\Services\Interface\DepartementsServiceInterface;
use App\Services\Interface\DocumentsServiceInterface;
use App\Services\Interface\FoldersServiceInterface;
use App\Services\Interface\UserServiceInterface;
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

        $this->app->bind(
            FolderRepositoryInterface::class,
            FolderRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            UserServiceInterface::class,
            AuthService::class
        );

        $this->app->bind(
            DecodageServiceInterface::class,
            DecodageService::class
        );

        $this->app->bind(
            DepartementsServiceInterface::class,
            DepartementsService::class
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
