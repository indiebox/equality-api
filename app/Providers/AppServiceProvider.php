<?php

namespace App\Providers;

use App\Services\Contracts\Image\ImageService as ImageServiceContract;
use App\Services\Contracts\Projects\LeaderService as LeaderServiceContract;
use App\Services\Image\ImageService;
use App\Services\Projects\LeaderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        ImageServiceContract::class => ImageService::class,
        LeaderServiceContract::class => LeaderService::class,
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
        Builder::macro('visible', function ($field, $value = null, $default = null) {
            $model = $this->getModel();

            if (func_num_args() < 3) {
                $default = new MissingValue();
            }

            if ($model == null) {
                return $default;
            }

            if (in_array($field, $model->getHidden())) {
                return $default;
            }

            if (!array_key_exists($field, $model->getAttributes())) {
                return $default;
            }

            return func_num_args() > 1
                ? value($value)
                : $model->{$field};
        });

        // Setup default password rule.
        Password::defaults(function () {
            $rule = Password::min(6);

            return $this->app->isProduction()
                        ? $rule->mixedCase()->numbers()
                        : $rule;
        });
    }
}
