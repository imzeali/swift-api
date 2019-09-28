<?php
/**
 * User: babybus zhili
 * Date: 2019-06-18 10:09
 * Email: <zealiemai@gmail.com>
 */



use Illuminate\Routing\Router;

Admin::routes();

$api_resources = [
];

Route::group([
    'prefix' => config('api.route.prefix'),
    'namespace' => config('api.route.namespace'),
    'middleware' => config('api.route.middleware'),
], function (Router $router) use ($api_resources) {

    foreach ($api_resources as $name => $controller) {
        $router->apiResource($name, $controller);
    }

});


