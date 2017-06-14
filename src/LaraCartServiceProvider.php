<?php

namespace Andskur\LaraCart;

use Illuminate\Support\ServiceProvider;

use Andskur\LaraCart\LaraCart;

class LaraCartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('LaraCart', function () {
            return new LaraCart();
        });

        $this->app->bind('Andskur\LaraCart\Storage\StorageContract', 'Andskur\LaraCart\Storage\StorageRedis');
    }
}
