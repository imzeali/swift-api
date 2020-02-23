<?php
/**
 * User: babybus zhili
 * Date: 2019-06-13 15:41
 * Email: <zealiemai@gmail.com>
 */


use SwiftApi\Controllers\AuthController;
use SwiftApi\Model\Administrator;
use SwiftApi\Model\Permissions;
use SwiftApi\Model\Roles;

return [
    'auth' => [

        'controller' => SwiftApi\Controllers\AuthController::class,

        'guards' => [
            'api' => [
                'driver' => 'token',
                'provider' => 'api',
            ],
        ],

        'providers' => [
            'api' => [
                'driver' => 'eloquent',
                'model' => SwiftApi\Model\Administrator::class,
            ],
        ],

        // Add "remember me" to login form
        'remember' => false,

        // Redirect to the specified URI when user is not authorized.
        'redirect_to' => 'auth/ding_talk_login',

        // The URIs that should be excluded from authorization.
        'excepts' => [
            'auth/login',
            'auth/ding_talk_login',
            'auth/callback_ding_talk_login',
        ],
    ],
    'directory' => app_path('Api'),
    'database' => [
        'namespace' => 'App\\Models',
        // Database connection for following tables.
        'connection' => '',

        // User tables and model.
        'users_table' => 'users',
        'users_model' => SwiftApi\Model\Administrator::class,

        // Role table and model.
        'roles_table' => 'roles',
        'roles_model' => SwiftApi\Model\Roles::class,

        // Permission table and model.
        'permissions_table' => 'permissions',
        'permissions_model' => SwiftApi\Model\Permissions::class,


        // Pivot table for table above.
        'operation_log_table' => 'operation_log',
        'user_permissions_table' => 'user_permissions',
        'role_users_table' => 'role_users',
        'role_permissions_table' => 'role_permissions',

        //
        'finite_state_machine_log_table' => 'fsm_logs',
        'finite_state_machine_log_model' => SwiftApi\Model\Fsmlogs::class,


    ],
    'route' => [

        'prefix' => env('API_ROUTE_PREFIX', 'api'),

        'namespace' => 'App\\Api\\Controllers',

        'middleware' => ['swift-api','bindings'],
    ],
    'request' => [
        'namespace' => 'App\\Api\\Requests',
    ],
    'dingtalk' => [
        'corp_id' => env('DING_TALK_CORP_ID'),
        'app_key' => env('DING_TALK_APP_KEY'),
        'app_secret' => env('DING_TALK_APP_SECRET'),
        'agent_id' => env('DING_TALK_AGENT_ID'),
        'token' => env('DING_TALK_TOKEN'),
        'aes_key' => env('DING_TALK_AES_KEY'),

        'oauth' => [
            'pc' => [
                'client_id' => env('DING_TALK_OAUTH_PC_CLIENT_ID'),
                'client_secret' => env('DING_TALK_OAUTH_PC_CLIENT_SECRET'),
                'redirect' => env('DING_TALK_OAUTH_PC_REDIRECT'),
                'scope' => 'snsapi_login',
            ],
        ]
    ],
];
