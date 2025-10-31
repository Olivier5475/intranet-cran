<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use phpCAS;

class CasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * @throws \Exception
     */
    public function boot(): void
    {
        $this->initCAS();
    }

    private function initCAS(): void {
        phpCAS::client(
            CAS_VERSION_2_0,
            config('cas.host'),
            config('cas.port'),
            config('cas.uri'),
            config('app.url'),
        );

//          todo : enlever ca de la prod
        phpCAS::setNoCasServerValidation();
//          todo : mettre cette ligne à la place
//        phpCAS::setCasServerCACert(config('cas.ca_cert_path'));
    }
}
