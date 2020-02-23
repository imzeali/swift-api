<?php
/**
 * User: babybus zhili
 * Date: 2019-06-17 14:12
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {

            $this->clearLoginAttempts($request);

            return ['token' => $this->guard()->user()->updateApiToken()];

        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {

        Auth::guard('api')->user()->updateApiToken();

        return redirect(config('api.route.prefix'));

    }

    public function getAuthUserInfo(){
        return Auth::guard('api')->user();
    }
}
