<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;
use App\Modules\Admin\Middleware\EmailVerifyMiddleware;
use App\Modules\Admin\Middleware\PhoneVerifyMiddleware;
use App\Modules\Admin\Middleware\EmailNewVerifyMiddleware;
use App\Modules\Admin\Middleware\PhoneNewVerifyMiddleware;
use App\Modules\Admin\Middleware\NewEmailUniqueMiddleware;
use App\Modules\Admin\Middleware\NewPhoneUniqueMiddleware;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;

/**
 * 修改密码
 */
class ChangeAccountController extends Controller {

    const EMAIL_CODE_KEY = 'change_account_email_code';
    const PHONE_CODE_KEY = 'change_account_phone_code';

    public function getRules() {
        return [];
    }

    /**
     * 登录
     * @param  Request $request
     * @desc 用户登陆
     * @return
     */
    public function index(Request $request) {

        $admin = Auth::guard('admin')->user();
        if ($request->post('type') == 'email' && !empty($admin->email)) {
            check(!empty(trim($request->post('email'))), Lang::get('user.new_email_required'));
            check(!empty(trim($request->post('code'))), Lang::get('user.email_code_required'));
            check(!empty(trim($request->post('new_code'))), Lang::get('user.new_email_code_required'));
        } elseif ($request->post('type') == 'phone' && !empty($admin->phone)) {
            check(!empty(trim($request->post('phone'))), Lang::get('user.new_phone_required'));
            check(!empty(trim($request->post('code'))), Lang::get('user.phone_code_required'));
            check(!empty(trim($request->post('new_code'))), Lang::get('user.new_phone_code_required'));
        } elseif ($request->post('type') == 'email' && empty($admin->email)) {
            check(!empty(trim($request->post('email'))), Lang::get('user.new_email_required'));
            check(!empty(trim($request->post('new_code'))), Lang::get('user.new_email_code_required'));
        } elseif ($request->post('type') == 'phone' && empty($admin->phone)) {
            check(!empty(trim($request->post('phone'))), Lang::get('user.new_phone_required'));
            check(!empty(trim($request->post('new_code'))), Lang::get('user.new_phone_code_required'));
        }
        $service = Admin::service('ChangeAccountService')
                ->with('type', trim($request->post('type')))
                ->with('email', trim($admin->email ?? ''))
                ->with('phone', trim($admin->phone ?? ''))
                ->with('user_id', $admin->user_id)
                ->with('new_email', trim($request->post('email') ?? ''))
                ->with('new_phone', trim($request->post('phone') ?? ''))
                ->with('code', trim($request->post('code') ?? ''))
                ->with('new_code', trim($request->post('new_code') ?? ''))
                ->with('email_code', trim($request->post('code') ?? ''))
                ->with('phone_code', trim($request->post('code') ?? ''))
                ->with('email_new_code', trim($request->post('new_code') ?? ''))
                ->with('phone_new_code', trim($request->post('new_code') ?? ''))
                ->with('email_code_key', trim(self::EMAIL_CODE_KEY . ':' . $admin->email))
                ->with('phone_code_key', trim(self::PHONE_CODE_KEY . ':' . $admin->phone))
                ->with('email_new_code_key', trim(self::EMAIL_CODE_KEY . ':' . trim($request->post('email') ?? '')))
                ->with('phone_new_code_key', trim(self::PHONE_CODE_KEY . ':' . trim($request->post('phone') ?? '')));



        if ($request->post('type') == 'phone' && !empty($admin->phone)) {
            $request->merge(['new_phone' => trim($request->post('phone')),
                'phone_code_key' => trim(self::PHONE_CODE_KEY . ':' . $admin->phone),
                'phone_new_code_key' => trim(self::PHONE_CODE_KEY . ':' . trim($request->post('phone') ?? '')),
                'new_code' => trim($request->post('new_code'))]);
            $service->setMiddleware([NewPhoneUniqueMiddleware::class, PhoneVerifyMiddleware::class, PhoneNewVerifyMiddleware::class]);
        } elseif ($request->post('type') == 'email' && !empty($admin->email)) {
            $request->merge(['new_email' => trim($request->post('email')),
                'email_code_key' => trim(self::EMAIL_CODE_KEY . ':' . $admin->email),
                'email_new_code_key' => trim(self::EMAIL_CODE_KEY . ':' . trim($request->post('email') ?? '')),
                'new_code' => trim($request->post('new_code'))]);
            $service->setMiddleware([NewEmailUniqueMiddleware::class, EmailVerifyMiddleware::class, EmailNewVerifyMiddleware::class]);
        } elseif ($request->post('type') == 'email' && empty($admin->email)) {
            $request->merge(['new_email' => trim($request->post('email')),
                'email_code_key' => trim(self::EMAIL_CODE_KEY . ':' . $admin->email),
                'email_new_code_key' => trim(self::EMAIL_CODE_KEY . ':' . trim($request->post('email') ?? '')),
                'new_code' => trim($request->post('new_code'))]);
            $service->setMiddleware([NewEmailUniqueMiddleware::class, EmailNewVerifyMiddleware::class]);
        } elseif ($request->post('type') == 'phone' && empty($admin->phone)) {
            $request->merge(['new_phone' => trim($request->post('phone')),
                'phone_new_code_key' => trim(self::PHONE_CODE_KEY . ':' . trim($request->post('phone') ?? '')),
                'new_code' => trim($request->post('new_code'))]);
            $service->setMiddleware([NewPhoneUniqueMiddleware::class, PhoneNewVerifyMiddleware::class]);
        }
        return $service->run();
    }

    /**
     * 发送验证码
     * @param  Request $request
     * @desc 注册时根据邮箱发送验证码
     * @return
     */
    public function phone(Request $request) {
        $admin = Auth::guard('admin')->user();
        $phone = $request->phone;
        $phoneCodeKey = self::PHONE_CODE_KEY . ':' . $phone;
        $request->merge(['phone' => $phone, 'phone_code_key' => $phoneCodeKey]);
        return Admin::service('PhoneChangeAcountService')
                        ->with('phone', $phone)
                        ->with('customer_id', $admin->user_id)
                        ->with('phone_code_key', $phoneCodeKey)
                        ->run('send');
    }

    /**
     * 发送验证码
     * @param  Request $request
     * @desc 注册时根据邮箱发送验证码
     * @return
     */
    public function email(Request $request) {
        $admin = Auth::guard('admin')->user();
        $email = $request->email;
        $emailCodeKey = self::EMAIL_CODE_KEY . ':' . $email;
        $request->merge(['email' => $email, 'email_code_key' => $emailCodeKey]);
        return Admin::service('EmailChangeAcountService')
                        ->with('email', $email)
                        ->with('user_id', $admin->user_id)
                        ->with('email_code_key', $emailCodeKey)
                        ->run('send');
    }

    /**
     * 发送验证码
     * @param  Request $request
     * @desc 注册时根据邮箱发送验证码
     * @return
     */
    public function emailVerify(Request $request) {
        $email = $request->email;
        $code = $request->email_code;
        $emailCodeKey = self::EMAIL_CODE_KEY . ':' . $email;
        $request->merge(['email' => $email, 'email_code_key' => $emailCodeKey, 'email_code' => $code]);
        return Admin::service('EmailChangeAcountService')
                        ->with('email', $email)
                        ->with('email_code', $code)
                        ->with('email_code_key', $emailCodeKey)
                        ->run('emailVerify');
    }

    /**
     * 发送验证码
     * @param  Request $request
     * @desc 注册时根据邮箱发送验证码
     * @return
     */
    public function phoneVerify(Request $request) {
        $phone = $request->phone;
        $code = $request->phone_code;
        $phoneCodeKey = self::PHONE_CODE_KEY . ':' . $phone;
        $request->merge(['phone' => $phone, 'phone_code_key' => $phoneCodeKey, 'phone_code' => $code]);
        return Admin::service('PhoneChangeAcountService')
                        ->with('phone', $phone)
                        ->with('phone_code', $code)
                        ->with('phone_code_key', $phoneCodeKey)
                        ->run('phoneVerify');
    }

}
