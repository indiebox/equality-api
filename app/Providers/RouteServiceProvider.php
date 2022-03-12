<?php

namespace App\Providers;

use App\Models\Invite;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->bindingsForScopes();
        $this->bindingsForInvites();

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api/v1')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api-v1.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by(auth()->id() ?: $request->ip());
        });

        RateLimiter::for('mail_verification', function (Request $request) {
            return Limit::perMinute(5)
                ->by(auth()->id());
        });
    }

    protected function bindingsForScopes()
    {
        // This macro used to get onlyTrashed models with SoftDeletes trait.
        // First parameter is model name (can be lowercased),
        // second parameter is optional and it is a column name.
        // Route::get('boards/{trashed:board}', ...)->can('someAction', 'trashed:board')
        // Route::get('boards/{trashed:board_name}, ...)->can('someAction', 'trashed:board')
        Route::bind('trashed', function ($id, RoutingRoute $route) {
            $bindings = Str::of($route->bindingFieldFor('trashed'))->explode('_');

            $model = app("App\\Models\\" . $bindings[0]);
            if ($model == null || !in_array(SoftDeletes::class, class_uses_recursive($model))) {
                throw (new ModelNotFoundException())->setModel(
                    get_class($model),
                    $id
                );
            }

            $model = $model::onlyTrashed()->where($bindings[1] ?? $model->getRouteKeyName(), $id)->firstOrFail();

            $route->setParameter('trashed:' . $bindings[0], $model);

            return $model;
        });
    }

    protected function bindingsForInvites()
    {
        Route::bind('pendingInvite', function ($id) {
            return Invite::onlyPending()->findOrFail($id);
        });
    }
}
