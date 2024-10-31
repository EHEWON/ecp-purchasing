<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Lang,
    Redis,
    Mail
};
use App\Modules\Admin\Mail\ChangeAccountMail;
use Illuminate\Support\Facades\Auth;
use App\Common\Models\{
    User,
    SendLog
};
use App\Modules\Admin\Middleware\{
    EmailUniqueMiddleware,
    EmailVerifyMiddleware
};

class EmailChangeAcountService extends Service {

    public $middleware = [
        EmailUniqueMiddleware::class => ['only' => 'send'],
        EmailVerifyMiddleware::class => ['only' => 'emailVerify'],
    ];
    public $beforeEvent = [];
    public $afterEvent = [];

    public function getRules() {
        return [
            'send' => [
                'email' => 'required|email',
                'email_code_key' => 'required'
            ]
        ];
    }

    public function getMessages() {
        return [
            'send' => [
                'email.required' => Lang::get('user.email_required'),
                'email.email' => Lang::get('user.email_valid'),
                'email_code_key.required' => Lang::get('user.email_code_key_required')
            ]
        ];
    }

    public function send(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $email = $request['email'];
        $emailCodeKey = $request['email_code_key'];

        $code = randomnum(6);

        $expired_seconds = config('forget.email_expired');

        if ($expired_seconds > 3600) {
            $expired = ($expired_seconds / 3600) . ($this->lang == 'zh' ? '小时' : ' hours');
        } else {
            $expired = ($expired_seconds / 60) . ($this->lang == 'zh' ? '分钟' : ' minutes');
        }
        if (strtolower($admin->email) !== strtolower($email)) {
            $count = User::where('email', $email)->where('user_id', '<>', $userId)->where('deleted_flag', 'N')->count();
            check($count === 0, Lang::get('user.email_account_already_exists'));
        }
        $lang = $this->lang;
        $user = ['email' => $email, 'code' => $code, 'expired' => $expired];
        $sendAt = date('Y-m-d H:i:s');
        $response = Mail::to($email)->locale($lang)
                ->send(new ChangeAccountMail($user));

        Redis::setex($emailCodeKey, $expired_seconds, $code);
        SendLog::insertGetId([
            'type' => 'email',
            'message_to' => $email,
            'title' => 'ChangeAccountMail',
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => $sendAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return [];
    }

    public function emailVerify(Request $request) {
        return [];
    }

}
