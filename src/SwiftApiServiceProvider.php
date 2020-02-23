<?php
/**
 * User: babybus zhili
 * Date: 2019-06-13 14:38
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;


class SwiftApiServiceProvider extends ServiceProvider
{
    protected $commands = [
        Console\ApiCommand::class,
        Console\InstallCommand::class,
        Console\UninstallCommand::class,
        Console\CreateApiCommand::class,
        Console\CreateRequrestCommand::class,
        Console\CreateModelCommand::class,
        Console\CreateRouteCommand::class,
        Console\CreateControllerCommand::class,
        Console\CreateMigrationCommand::class,
        Console\CreateViewCommand::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'swift-api.auth' => Middleware\Authenticate::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'swift-api' => [
            'swift-api.auth',
        ],
    ];


    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config' => config_path()], 'swift-api-config');
            $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'swift-api-migrations');
        }

        if (file_exists($routes = api_path('routes.php'))) {
            $this->loadRoutesFrom($routes);
        }


    }

    public function register()
    {
        $this->loadApiAuthConfig();

        $this->registerRouteMiddleware();
        $this->commands($this->commands);

    }

    protected function loadApiAuthConfig()
    {
        config(Arr::dot(config('api.auth', []), 'auth.'));
    }

    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }
}
