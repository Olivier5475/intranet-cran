<?php

namespace App\Providers;

use App\Repositories;
use App\Services;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
//        SERVICES BINDING
        $this->app->bind(
            Services\Interfaces\FoldersServiceInterface::class,
            Services\FoldersService::class
        );

        $this->app->bind(
            Services\Interfaces\DocumentsServiceInterface::class,
            Services\DocumentService::class
        );

        $this->app->bind(
            Services\Interfaces\FilesServiceInterface::class,
            Services\FilesService::class
        );

        $this->app->bind(
            Services\Interfaces\UserServiceInterface::class,
            Services\AuthService::class
        );

        $this->app->bind(
            Services\Interfaces\DecodageServiceInterface::class,
            Services\DecodageService::class
        );

        $this->app->bind(
            Services\Interfaces\DepartementsServiceInterface::class,
            Services\DepartementsService::class
        );

        $this->app->bind(
            Services\Interfaces\AttachmentServiceInterface::class,
            Services\AttachmentService::class
        );

        $this->app->bind(
            Services\Interfaces\VersionsServiceInterface::class,
            Services\VersionsService::class
        );

        $this->app->bind(
            Services\Interfaces\MapDTOServiceInterface::class,
            Services\MapDTOService::class
        );
//        REPOSITORY BINDING
        $this->app->bind(
            Repositories\Interfaces\FolderRepositoryInterface::class,
            Repositories\FolderRepository::class
        );

        $this->app->bind(
            Repositories\Interfaces\DepartementRepositoryInterface::class,
            Repositories\DepartementRepository::class
        );
        $this->app->bind(
            Repositories\Interfaces\UserRepositoryInterface::class,
            Repositories\UserRepository::class
        );

        $this->app->bind(
            Repositories\Interfaces\DocumentRepositoryInterface::class,
            Repositories\DocumentRepository::class
        );

        $this->app->bind(
            Repositories\Interfaces\AttachmentRepositoryInterface::class,
            Repositories\AttachmentRepository::class
        );

        $this->app->bind(
            Repositories\Interfaces\FilesRepositoryInterface::class,
            Repositories\FilesRepository::class
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
