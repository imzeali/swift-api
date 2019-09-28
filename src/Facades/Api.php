<?php

namespace SwiftApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Admin.
 *
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null user()
 * @method static string title()
 * @method static void registerAuthRoutes()

 *
 *
 */
class Api extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SwiftApi\Api::class;
    }
}
