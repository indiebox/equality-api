<?php

namespace App\Providers;

use App\Services\Contracts\Image\ImageService as ImageServiceContract;
use App\Services\Contracts\Projects\LeaderService as LeaderServiceContract;
use App\Services\Image\ImageService;
use App\Services\Projects\LeaderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Znck\Eloquent\Relations\BelongsToThrough;

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
        /**
         * This method returns the attribute value or `MissingValue` if
         * this attribute is hidden.
         *
         * @param array|string $field
         * @param mixed $value
         * @param mixed $default
         * @return mixed
         *
         * @example / Example 1
         * ```
         * $model = new Model(['name' => 'test']);
         * $model->visible('name') //return 'test';
         * $model->visible('name', $model->name . "_suffix") //return 'test_suffix'
         * $model->visible('name', fn() => $model->name . "_suffix") //return 'test_suffix'
         * $model->visible('desc') //return null
         *
         * $model->makeHidden(['name']);
         * $model->visible('name') //return new MissingValue
         * $model->visible('name', $model->name, 'default') //return 'default'
         * ```
         *
         * @example / Example 2
         * ```
         * $model = new Model(['name' => 'test']);
         * $model->visible(['name']) //return ['name' => 'test'];
         * $model->visible(['name' => $model->name . "_suffix"]) //return ['name' => 'test_suffix']
         * $model->visible(['desc']) //return ['desc' => null]
         *
         * // All 'merged' attributes will not be checked for visibility.
         * // Merged attributes very good for include relations and things like that.
         * $model->visible(['desc'], ['merged' => 'test']) //return ['desc' => null, 'merged' => 'test']
         *
         * $model->makeHidden(['name']);
         * $model->visible(['name', 'desc']) //return ['name' => new MissingValue, 'desc' => null]
         * $model->visible(['name' => 'other', 'desc' => 'test']) //return ['name' => new MissingValue, 'desc' => 'test']
         * ```
         */
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

                    if (in_array($fieldName, $model->getHidden())) {
                        $fieldValue = $default;
                    } else {
                        $fieldValue = $isKeyValuePairs ? value($fieldValue) : $model->{$fieldValue};
                    }

                    $result->add([$fieldName => $fieldValue]);
                }

                if (func_num_args() > 1 && is_iterable($value)) {
                    $result = $result->merge([$value]);
                }

                return $result->collapse()->toArray();
            }

            if (in_array($field, $model->getHidden())) {
                return $default;
            }

            return func_num_args() > 1
                ? value($value)
                : $model->{$field};
        });

        /**
         * Indicate that trashed "through" parents should be included in the query.
         *
         * @return \Znck\Eloquent\Relations\BelongsToThrough
         */
        BelongsToThrough::macro('withTrashedParents', function () {
            $columns = [];

            foreach ($this->getThroughParents() as $parent) {
                if (in_array(SoftDeletes::class, class_uses_recursive(get_class($parent)))) {
                    $columns[] = $parent->getQualifiedDeletedAtColumn();
                }
            }

            if (empty($columns)) {
                return $this;
            }

            return $this->withTrashed($columns);
        });
    }
}
