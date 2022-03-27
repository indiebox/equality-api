<?php

namespace App\Providers;

use App\Services\Contracts\Image\ImageService as ImageServiceContract;
use App\Services\Contracts\Projects\LeaderService as LeaderServiceContract;
use App\Services\Image\ImageService;
use App\Services\Projects\LeaderService;
use Illuminate\Database\Eloquent\SoftDeletes;
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
