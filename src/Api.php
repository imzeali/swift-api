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
//            'middleware' => config('api.route.middleware'),
        ];
        $authController = config('api.auth.controller', AuthController::class);

        app('router')->group($attributes, function ($router) use ($authController) {

            $router->namespace('\SwiftApi\Controllers')->group(function ($router) {
                $router->get('database_tool/show_table', 'DatabaseToolController@showTable')->name('api.database_tool.show_table');
                $router->get('database_tool/get_table_columns/{table_name}', 'DatabaseToolController@getTableColumns')->name('api.database_tool.get_table_columns');
                $router->post('helper/scaffold', 'HelperController@postScaffold')->name('api.helper.post_scaffold');
                $router->get('helper/scaffold', 'HelperController@getScaffold')->name('api.helper.get_scaffold');
                $router->post('helper/hand_of_god', 'HelperController@handOfGod')->name('api.helper.hand_of_god');
            });

            $router->get('auth/ding_talk_login', $authController . '@getDingTalkLogin')->name('api.ding_talk_login');
            $router->get('auth/callback_ding_talk_login', $authController . '@callbackDingTalkLogin')->name('api.callback_ding_talk_login');
            $router->post('auth/logout', $authController . '@logout')->name('api.logout');
            $router->post('auth/login', $authController . '@login')->name('api.login');
        });

        $attributes['middleware'] = config('api.route.middleware');

        app('router')->group($attributes, function ($router) use ($authController) {
            $router->get('user/info', $authController . '@getAuthUserInfo')->name('api.get_auth_use_info');
        });

    }

}
