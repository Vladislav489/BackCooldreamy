<?php

namespace App\Providers;

use App\Mail\UnisenderMailer\UnisenderApi;
use App\Mail\UnisenderMailer\UnisenderTransport;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//		if (class_exists(TelescopeApplicationServiceProvider::class)) {
//            $this->app->register(TelescopeServiceProvider::class);
//        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Transport for Unisender
        \Mail::extend('unisender', function (array $config = []) {
            $api = new UnisenderApi($config);
            return new UnisenderTransport($api);
        });
    }
}
