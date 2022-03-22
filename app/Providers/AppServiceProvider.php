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
        // Setup default password rule.
        Password::defaults(function () {
            $rule = Password::min(6);

            return $this->app->isProduction()
                        ? $rule->mixedCase()->numbers()
                        : $rule;
        });

        $this->registerQueryMacros();
    }

    protected function registerQueryMacros()
    {
        Builder::macro('visible', function ($field, $value = null, $default = null) {
            $model = $this->getModel();

            if (func_num_args() < 3) {
                $default = new MissingValue();
            }

            if ($model == null) {
                return $default;
            }

            if (is_array($field)) {
                $result = collect();

                foreach ($field as $key => $fieldValue) {
                    $isKeyValuePairs = !is_numeric($key);
                    $fieldName = $isKeyValuePairs ? $key : $fieldValue;

                    if (
                        in_array($fieldName, $model->getHidden())
                        || !array_key_exists($fieldName, $model->getAttributes())
                    ) {
                        $fieldValue = $default;
                    } else {
                        $fieldValue = $isKeyValuePairs ? $fieldValue : $model->{$fieldValue};
                    }

                    $result->add([$fieldName => $fieldValue]);
                }

                if (func_num_args() > 1 && is_iterable($value)) {
                    $result = $result->merge([$value]);
                }

                return $result->collapse()->toArray();
            }

            // Check if attribute is visible and exists in model.
            if (
                in_array($field, $model->getHidden())
                || !array_key_exists($field, $model->getAttributes())
            ) {
                return $default;
            }

            return func_num_args() > 1
                ? value($value)
                : $model->{$field};
        });
    }
}
