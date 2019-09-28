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
use SwiftApi\Api;
use SwiftApi\Model\DingUsers;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    protected function loginDingTalkValidator(array $data)
    {
        return Validator::make($data, [
            'code' => 'required',
        ]);
    }

    public function callbackDingTalkLogin(Request $request)
    {
        $this->loginDingTalkValidator($request->all())->validate();

        $ding_talk = app('DingTalk');
        $credential = config('api.dingtalk.oauth.pc');
        $oauth = $ding_talk->oauth->use('pc');
        $data = [
            'tmp_auth_code' => $request->input('code'),
        ];
        $query = [
            'accessKey' => $credential['client_id'],
            'timestamp' => $timestamp = (int)microtime(true) * 1000,
            'signature' => $oauth->signature($timestamp),
        ];
        $ding_response = $oauth->postJson('sns/getuserinfo_bycode', $data, $query);
        if ($ding_response['errcode'] == 0) {
            $ding_user_info = $ding_response['user_info'];
            $unionid = $ding_user_info['unionid'];
            $ding_user_response = $ding_talk->user->getUseridByUnionid($unionid);
            if ($ding_user_response['errcode'] == 0) {
                $ding_user_id = $ding_user_response['userid'];

                if ($ding_users = config('api.database.ding_users_model')::where('userid', '=', $ding_user_id)->first()) {

                    return ['token' => $ding_users->user->updateApiToken()];

                } else {
                    $ding_user_details = $ding_talk->user->get($ding_user_id, $lang = null);

                    if (array_get($ding_user_details,'errcode') == 0) {

                        return DB::transaction(function () use ($ding_user_details, $unionid) {
                            $new_users = config('api.database.users_model')::create([
                                'name' => array_get($ding_user_details,'name'),
                                'username' => array_get($ding_user_details,'email'),
                                'avatar' => array_get($ding_user_details,'avatar'),
                                'email' => array_get($ding_user_details,'email'),
                            ]);

                            $ding_user=[];
                            $ding_user['unionid'] = $unionid;
                            $ding_user['users_id'] = $new_users->id;
                            $ding_user['tags'] = json_encode(array_get($ding_user_details,'tags'));
                            $ding_user['extattr'] = json_encode(array_get($ding_user_details,'extattr'));
                            $ding_user['roles'] = json_encode(array_get($ding_user_details,'roles'));
                            $ding_user['department'] = json_encode(array_get($ding_user_details,'department'));
                            $ding_user['openid'] = array_get($ding_user_details,'openId');
                            $ding_user['userid'] = array_get($ding_user_details,'userid');
                            $ding_user['orderInDepts'] = array_get($ding_user_details,'orderInDepts');
                            $ding_user['position'] = array_get($ding_user_details,'position');
                            $ding_user['isSenior'] = array_get($ding_user_details,'isSenior');
                            $ding_user['workPlace'] = array_get($ding_user_details,'workPlace');
                            $ding_user['isBoss'] = array_get($ding_user_details,'isBoss');
                            $ding_user['name'] = array_get($ding_user_details,'name');
                            $ding_user['stateCode'] = array_get($ding_user_details,'stateCode');
                            $ding_user['avatar'] = array_get($ding_user_details,'avatar');
                            $ding_user['jobnumber'] = array_get($ding_user_details,'jobnumber');
                            $ding_user['isLeaderInDepts'] = array_get($ding_user_details,'isLeaderInDepts');
                            $ding_user['active'] = array_get($ding_user_details,'active');
                            $ding_user['isAdmin'] = array_get($ding_user_details,'isAdmin');
                            $ding_user['hiredDate'] = array_get($ding_user_details,'hiredDate');
                            $ding_user['mobile'] = array_get($ding_user_details,'mobile');
                            $ding_user['isHide'] = array_get($ding_user_details,'isHide');
                            $ding_user['email'] = array_get($ding_user_details,'email');
                            $ding_user['orgEmail'] = array_get($ding_user_details,'orgEmail');
                            $ding_user['remark'] = array_get($ding_user_details,'remark');
                            $ding_user['tel'] = array_get($ding_user_details,'tel');

                            config('api.database.ding_users_model')::create($ding_user);

                            return ['token' => $new_users->updateApiToken()];

                        }, 5);

                    } else {
                        return $ding_user_details;
                    }
                }

            } else {
                abort(400,$ding_user_response['errmsg']);
            }
        } else {
            abort(400,$ding_response['errmsg']);
        }
    }

    public function getDingTalkLogin()
    {

        $app_id = config('api.dingtalk.oauth.pc.client_id');
        $redirect_url = route('api.callback_ding_talk_login');
        return redirect()->guest("https://oapi.dingtalk.com/connect/qrconnect?appid={$app_id}&response_type=code&scope=snsapi_login&state=STATE&redirect_uri={$redirect_url}");
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
