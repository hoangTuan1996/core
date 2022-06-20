<?php

namespace MediciVN\Core\Providers;

use Illuminate\Support\ServiceProvider;

class MediciCoreHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        foreach (glob(dirname(__DIR__) . '/Helpers/*.php') as $file) {
            require_once $file;
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
