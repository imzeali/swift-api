<?php

namespace SwiftApi\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $redirectTo = api_base_path(config('api.auth.redirect_to', 'auth/ding_talk_login'));

        if (Auth::guard('api')->guest() && !$this->shouldPassThrough($request)) {
            return abort(401);
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = config('api.auth.excepts', [
            'auth/login',
            'auth/ding_talk_login',
            'auth/callback_ding_talk_login',
        ]);

        return collect($excepts)
            ->map('api_base_path')
            ->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                return $request->is($except);
            });
    }
}
