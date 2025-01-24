<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::macro('mindinabox', function () {
            Http::withToken(config('services.mindinabox.api_key'))
                ->acceptJson()
                ->asJson()
                ->baseUrl(config('services.mindinabox.base_uri'))
                ->withQueryParameters([
                    'model' => config('services.mindinabox.model'),
                ]);
        });
    }
}
