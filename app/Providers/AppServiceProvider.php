<?php

namespace App\Providers;

use App\Services\Contracts\Image\ImageService as ImageServiceContract;
use App\Services\Contracts\Projects\LeaderService as LeaderServiceContract;
use App\Services\Image\ImageService;
use App\Services\Projects\LeaderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
        $this->registerBuilderMacros();

        // Setup default password rule.
        Password::defaults(function () {
            $rule = Password::min(6);

            return $this->app->isProduction()
                        ? $rule->mixedCase()->numbers()
                        : $rule;
        });
    }

    protected function registerBuilderMacros()
    {
        Builder::macro('loadRequested', function ($allowedRelations = null, $defaultRelations = []) {
            $model = $this->getModel();

            $model->load($defaultRelations);

            $requestedQueryString = request()->query('include');
            if ($requestedQueryString == null) {
                return;
            }

            $requestedRelations = explode(",", $requestedQueryString);

            foreach ($requestedRelations as $key => $requestedRelation) {
                $isAllowed = Arr::first($allowedRelations, fn($value) => $value === $requestedRelation) != null;

                if (!$isAllowed) {
                    unset($requestedRelations[$key]);

                    continue;
                }

                if (Str::endsWith($requestedRelation, ['_count', '_exists'])) {
                    continue;
                }

                $model->load($requestedRelation);

                unset($requestedRelations[$key]);
            }

            foreach ($requestedRelations as $requestedRelationAggregate) {
                $parsedAggregate = explode("_", $requestedRelationAggregate);
                $relationName = $parsedAggregate[0];
                $aggregateType = $parsedAggregate[1];

                switch ($aggregateType) {
                    case "count":
                        if ($model->relationLoaded($relationName)) {
                            $model->{$requestedRelationAggregate} = count($model->{$relationName});
                        } else {
                            $model->loadCount($relationName);
                        }

                        break;
                    case "exists":
                        if ($model->relationLoaded($relationName)) {
                            $model->{$requestedRelationAggregate} = $model->{$relationName} != null;
                        } else {
                            $model->loadExists($relationName);
                        }
                        break;
                    default:
                        break;
                }
            }
        });
    }
}
