<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use Illuminate\Http\Request;
use App\Common\Models\User;
use Illuminate\Support\Facades\{
    Redis,
    Lang,
    Auth
};
use App\Modules\Admin\Events\ChangeSuccessEvent;

class ChangeAccountService extends Service {

    public $middleware = [];
    public $beforeEvent = [];
    public $afterEvent = [
        ChangeSuccessEvent::class,
    ];

    public function getRules() {
        return [
        ];
    }

    public function getMessages() {
        return [
        ];
    }

    public function handle(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        if ($request->type == 'phone') {
            $admin->phone = trim($request->post('new_phone'));
            User::where('user_id', $userId)->update([
//                'changed_at' => date('Y-m-d H:i:s'),
                'phone' => trim($request->post('new_phone'))
            ]);
        } else {
            $admin->phone = trim($request->post('new_email'));
            User::where('user_id', $userId)->update([
                'email' => trim($request->post('new_email'))
            ]);
        }
        Auth::guard('admin')->setUser($admin);
        if ($request->type === 'email') {
            Redis::del($request['email_code_key']);
            Redis::del($request['email_new_code_key']);
        } elseif ($request->type === 'phone') {
            Redis::del($request['phone_code_key']);
            Redis::del($request['phone_new_code_key']);
        }

        return [];
    }

    public function emailVerify(Request $request) {
        $customerType = $request['customer_type'];
        $code = Redis::get($request['email_code_key'] . ':' . $customerType);
        $email_code = trim($request['email_code']) ?? '';
        check(!empty($email_code) && strtolower($code) === strtolower(trim($email_code)), Lang::get('user.email_code_verify'));
        return true;
    }

    public function phoneVerify(Request $request) {
        $code = Redis::get($request['phone_code_key']);
        $phone_code = trim($request['phone_code']) ?? '';
        check(!empty($phone_code) && strtolower($code) === strtolower(trim($phone_code)), Lang::get('user.phone_verify'));
        return true;
    }

}
