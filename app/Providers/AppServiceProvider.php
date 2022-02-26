<?php

namespace App\Providers;

use App\Services\Contracts\Image\ImageService as ImageServiceContract;
use App\Services\Image\ImageService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        ImageServiceContract::class => ImageService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Setup default password rule.
        Password::defaults(function() {
            $rule = Password::min(6);

            return $this->app->isProduction()
                        ? $rule->mixedCase()->numbers()
                        : $rule;
        });
    }
}
