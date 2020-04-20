<?php

namespace SwiftApi;

use Illuminate\Support\Facades\Auth;
use SwiftApi\Controllers\AuthController;

/**
 * Class Admin.
 */
class Api
{

    const VERSION = '1.0.0';

    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('api.title');
    }

    public static function getLongVersion()
    {
        return sprintf('swift-api <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    public function user()
    {
        return Auth::guard('api')->user();
    }


    public function registerAuthRoutes()
    {
        $this->routes();
    }


    public function routes()
    {
        $attributes = [
            'prefix' => config('api.route.prefix'),
        ];
        $authController = config('api.auth.controller', AuthController::class);

        app('router')->group($attributes, function ($router) use ($authController) {
            $router->post('auth/logout', $authController . '@logout')->name('api.logout');
            $router->post('auth/login', $authController . '@login')->name('api.login');
        });

        $attributes['middleware'] = config('api.route.middleware');

        app('router')->group($attributes, function ($router) use ($authController) {
            $router->get('auth/user/info', $authController . '@getAuthUserInfo')->name('api.get_auth_use_info');
        });

    }

}
