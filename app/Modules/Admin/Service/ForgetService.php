<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Common\Models\{
    User,
    SendLog
};
use Illuminate\Http\Request;
use App\Modules\Admin\Middleware\{
    PhoneVerifyMiddleware,
    EmailVerifyMiddleware,
    AccountVerifyMiddleware
};
use App\Modules\Admin\Events\ResetPwdSuccessEvent;
use App\Modules\Admin\Mail\ForgetMail;
use Illuminate\Support\Facades\{
    Lang,
    Mail,
    Redis
};

/**
 * Description of ForgetService
 *
 * @author Administrator
 */
class ForgetService extends Service {

    protected $guard = 'admin';
    //put your code here
    public $middleware = [
        PhoneVerifyMiddleware::class => ['only' => ['phoneReset', 'phoneValid']],
        EmailVerifyMiddleware::class => ['only' => ['reset', 'valid']],
        AccountVerifyMiddleware::class => ['only' => ['accountReset', 'accountValid']],
    ];
    public $beforeEvent = [];
    public $afterEvent = [
        ResetPwdSuccessEvent::class => ['only' => ['reset', 'phoneReset', 'accountReset']],
    ];

    public function getRules() {
        return [
            'phonevalid' => [
                'phone' => 'required',
                'phone_code' => 'required',
            ],
            'phone' => [
                'phone' => 'required|exists:user',
                'phone_code_key' => 'required'
            ],
            'account' => [
                'account' => 'required',
                'account_code_key' => 'required'
            ],
            'accountValid' => [
                'account' => 'required',
                'account_code' => 'required',
            ],
            'accountReset' => [
                'password' => 'required|confirmed|min:6',
                'account' => 'required',
                'account_code' => 'required',
            ],
            'email' => [
                'email' => 'required|email:rfc,dns|exists:user,email',
                'email_code_key' => 'required'
            ],
            'valid' => [
                'email' => 'required',
                'email_code' => 'required',
            ],
            'phoneValid' => [
                'phone' => 'required',
                'phone_code' => 'required',
            ],
            'reset' => [
                'password' => 'required|confirmed|min:6',
                'email' => 'required',
                'email_code' => 'required',
            ],
            'phoneReset' => [
                'password' => 'required|confirmed|min:6',
                'phone' => 'required',
                'phone_code' => 'required',
            ]
        ];
    }

    public function getMessages() {
        return [
            'phonevalid' => [
                'phone.required' => Lang::get('user.phone_required'),
                'phone_code.required' => Lang::get('user.phone_code_required'),
            ],
            'phone' => [
                'phone.required' => Lang::get('user.phone_required'),
                'phone.exists' => Lang::get('user.phone_not_exists'),
                'phone_code_key.required' => Lang::get('user.email_code_key_required')
            ],
            'email' => [
                'email.required' => Lang::get('user.email_required'),
                'email.email' => Lang::get('user.email_valid'),
                'email.exists' => Lang::get('user.email_not_exists'),
                'email_code_key.required' => Lang::get('user.email_code_key_required')
            ],
            'account' => [
                'account.required' => Lang::get('user.account_required'),
                'account.required' => Lang::get('user.account_code_key_required')
            ],
            'valid' => [
                'email.required' => Lang::get('user.email_required'),
                'email_code.required' => Lang::get('user.email_code_required'),
            ],
            'accountValid' => [
                'account.required' => Lang::get('user.account_required'),
                'account_code.required' => Lang::get('user.account_code_required'),
            ],
            'phoneValid' => [
                'phone.required' => Lang::get('user.phone_required'),
                'phone_code.required' => Lang::get('user.phone_code_required'),
            ],
            'reset' => [
                'password.required' => Lang::get('user.password_required'),
                'password.confirmed' => Lang::get('user.password_confirmed'),
                'password.min' => Lang::get('user.password_min'),
                'email.required' => Lang::get('user.email_required'),
                'email_code.required' => Lang::get('user.email_code_required'),
            ],
            'accountReset' => [
                'password.required' => Lang::get('user.password_required'),
                'password.confirmed' => Lang::get('user.password_confirmed'),
                'password.min' => Lang::get('user.password_min'),
                'account.required' => Lang::get('user.account_required'),
                'account_code.required' => Lang::get('user.account_code_required'),
            ],
            'phoneReset' => [
                'password.required' => Lang::get('user.password_required'),
                'password.confirmed' => Lang::get('user.password_confirmed'),
                'password.min' => Lang::get('user.password_min'),
                'phone.required' => Lang::get('user.phone_required'),
                'phone_code.required' => Lang::get('user.phone_code_required'),
            ]
        ];
    }

    protected $model;

    public function __construct() {
        parent::__construct();
    }

    public function email(Request $request) {
        $email = $request->post('email');
        $emailCodeKey = $request['email_code_key'];
        $code = randomnum(6);
        $expired_seconds = config('forget.email_expired');
        if ($expired_seconds > 3600) {
            $expired = ($expired_seconds / 3600) . ($this->lang == 'zh' ? '小时' : ' hours');
        } else {
            $expired = ($expired_seconds / 60) . ($this->lang == 'zh' ? '分钟' : ' minutes');
        }
        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        $sendAt = date('Y-m-d H:i:s');
        $response = '';
        Mail::to($email)->send(new ForgetMail($user));
        Redis::setex($emailCodeKey, $expired_seconds, $code);
        SendLog::insertGetId([
            'type' => 'email',
            'message_to' => $email,
            'title' => 'ForgetMail',
            'message' => json_encode($user),
            'status' => '',
            'return' => $response,
            'send_at' => $sendAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return [];
    }

    public function reset(Request $request) {
        User::where('email', $request->post('email'))
                ->update([
                    'password' => password_hash($request->post('password'), PASSWORD_DEFAULT)
                        ]
        );
        Redis::del($request['email_code_key']);
        return [];
    }

    public function valid(Request $request) {
        return [];
    }

    public function accountValid(Request $request) {
        return [];
    }

    public function accountReset(Request $request) {
        $account = $request->post('account');
        User::where(function($q)use($account) { {
                        $q->where('email', $account)
                        ->orWhere('phone', $account);
                    }
                })
                ->update([
                    'password' => password_hash($request->post('password'), PASSWORD_DEFAULT)
                        ]
        );
        Redis::del($request->get('account_code_key'));
        return [];
    }

    public function account(Request $request) {
        $account = $request->post('account');
        $count = User::where(function($q)use($account) { {
                        $q->where('email', $account);
                    }
                })
                ->where('deleted_flag', 'N')
                ->count();
        if (empty($count)) {
            check(false, '账号不存在');
        }
        $accountCodeKey = $request['account_code_key'];
        $code = randomnum(6);
        $expired_seconds = config('forget.email_expired');
        if ($expired_seconds > 3600) {
            $expired = ($expired_seconds / 3600) . ($this->lang == 'zh' ? '小时' : ' hours');
        } else {
            $expired = ($expired_seconds / 60) . ($this->lang == 'zh' ? '分钟' : ' minutes');
        }

        $user = ['email' => $account, 'code' => $code, 'expired' => $expired];
        if (isEmail($account)) {
            $type = 'email';
            $title = 'ForgetPhone';
            $response = Mail::to($account)->send(new ForgetMail($user));
        } else {
            check(false, '邮箱号不正确');
        }
        $sendAt = date('Y-m-d H:i:s');
        Redis::setex($accountCodeKey, $expired_seconds, $code);
        SendLog::insertGetId([
            'type' => $title,
            'message_to' => $account,
            'title' => 'ForgetPhone',
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => $sendAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return [];
    }

}
