<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Ports\HttpClientPort;
use App\Adapters\HttpClientAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            config(['database.connections.mysql.host' => '127.0.0.1']);
        }
        $this->app->bind(HttpClientPort::class, HttpClientAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
