<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;
use App\Common\Models\User;
use Illuminate\Support\Facades\Lang;

/**
 * 忘记密码
 */
class ForgetController extends Controller {

    const EMAIL_CODE_KEY = 'reset_email_code';
    const PHONE_CODE_KEY = 'reset_phone_code';
    const ACCOUNT_CODE_KEY = 'reset_account_code';

    public function getRules() {
        return [];
    }

    /**
     * 重置密码
     * @param  Request $request 
     * @desc 用户登陆
     * @return 
     */
    public function reset(Request $request) {
        $email = $request->post('email');
        $user = User::where('email', trim($email))->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.email_not_exists'));
        $emailCodeKey = self::EMAIL_CODE_KEY . ':' . $email;
        $request->merge(['email_code_key' => $emailCodeKey]);
        return Admin::service('ForgetService')
            ->with('email', $request->post('email'))
            ->with('password', $request->post('password'))
            ->with('password_confirmation', $request->post('password_confirmation'))
            ->with('email_code', $request->post('email_code'))
            ->with('email_code_key', $emailCodeKey)
            ->run('reset');
    }

    /**
     * 重置密码
     * @param  Request $request 
     * @desc 用户登陆
     * @return 
     */
    public function phoneReset(Request $request) {
        $phone = $request->post('phone');
        $user = User::where('phone', trim($phone))->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.phone_not_exists'));
        $phoneCodeKey = self::PHONE_CODE_KEY . ':' . $phone;
        $request->merge(['phone_code_key' => $phoneCodeKey]);
        return Admin::service('ForgetService')
            ->with('phone', $request->post('phone'))
            ->with('password', $request->post('password'))
            ->with('password_confirmation', $request->post('password_confirmation'))
            ->with('phone_code', $request->post('phone_code'))
            ->with('phone_code_key', $phoneCodeKey)
            ->run('phoneReset');
    }

    /**
     * 校验邮箱验证码
     * @param  Request $request 
     * @return 
     */
    public function phonevalid(Request $request) {
        $phone = $request->post('phone');
        $user = User::where('phone', trim($phone))->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.phone_not_exists'));
        $phoneCodeKey = self::PHONE_CODE_KEY . ':' . $phone;
        $request->merge(['phone_code_key' => $phoneCodeKey,]);
        return Admin::service('ForgetService')
            ->with('phone', $request->post('phone'))
            ->with('phone_code', $request->post('phone_code'))
            ->with('phone_code_key', $phoneCodeKey)
            ->run('phoneValid');
    }

    /**
     * 校验邮箱验证码
     * @param  Request $request 
     * @return 
     */
    public function valid(Request $request) {
        $email = $request->post('email');
        $user = User::where('email', trim($email))->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.email_not_exists'));
        $emailCodeKey = self::EMAIL_CODE_KEY . ':' . $email;
        $request->merge(['email_code_key' => $emailCodeKey]);
        return Admin::service('ForgetService')
            ->with('email', $request->post('email'))
            ->with('email_code', $request->post('email_code'))
            ->with('email_code_key', $emailCodeKey)
            ->run('valid');
    }

    /**
     * 发送验证码
     * @param  Request $request 
     * @desc 注册时根据邮箱发送验证码
     * @return 
     */
    public function email(Request $request) {
        $email = $request->post('email');
        $user = User::where('email', trim($email))->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.email_not_exists'));
        $emailCodeKey = self::EMAIL_CODE_KEY . ':' . $email;
        $request->merge(['email_code_key' => $emailCodeKey]);
        return Admin::service('ForgetService')
            ->with('email', $email)
            ->with('email_code_key', $emailCodeKey)
            ->run('email');
    }

    /**
     * 发送验证码
     * @param  Request $request 
     * @desc 注册时根据邮箱发送验证码
     * @return 
     */
    public function phone(Request $request) {

        $phone = $request->post('phone');
        $user = User::where('phone', trim($phone))->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.email_not_exists'));
        $phoneCodeKey = self::PHONE_CODE_KEY . ':' . $phone;
        $request->merge(['phone_code_key' => $phoneCodeKey]);
        return Admin::service('ForgetService')
            ->with('phone', $phone)
            ->with('phone_code_key', $phoneCodeKey)
            ->run('phone');
    }

    /**
     * 发送验证码
     * @param  Request $request 
     * @desc 注册时根据邮箱发送验证码
     * @return 
     */
    public function account(Request $request) {
        $account = $request->post('account');
        $user = User::where(function($q)use($account) { {
                    $q->where('email', $account)
                    ->orWhere('phone', $account);
                }
            })
            ->where('deleted_flag', 'N')->first();
            
            
        check(!empty($user), Lang::get('user.account_not_exists'));
        $accountCodeKey = self::ACCOUNT_CODE_KEY . ':' . $account;
        $request->merge(['account_code_key' => $accountCodeKey]);
        return Admin::service('ForgetService')
            ->with('account', $account)
            ->with('account_code_key', $accountCodeKey)
            ->run('account');
    }

    /**
     * 校验邮箱验证码
     * @param  Request $request 
     * @return 
     */
    public function accountValid(Request $request) {
        $account = $request->post('account');
        $user = User::where(function($q)use($account) { {
                  $q->where('email', $account)
                    ->orWhere('phone', $account);
              }
          })->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.account_not_exists'));
        $accountCodeKey = self::ACCOUNT_CODE_KEY . ':' . $account;
        $request->merge(['account_code_key' => $accountCodeKey]);
        return Admin::service('ForgetService')
            ->with('account', $account)
            ->with('account_code_key', $accountCodeKey)
            ->with('account_code', $accountCodeKey)
            ->run('accountValid');
    }

    /**
     * 重置密码
     * @param  Request $request 
     * @desc 用户登陆
     * @return 
     */
    public function accountReset(Request $request) {
        $account = $request->post('account');
        $user = User::where(function($q)use($account) { {
                  $q->where('email', $account)
                    ->orWhere('phone', $account);
              }
          })->where('deleted_flag', 'N')->first();
        check(!empty($user), Lang::get('user.account_not_exists'));
        $accountCodeKey = self::ACCOUNT_CODE_KEY . ':' . $account;
        $request->merge(['account_code_key' => $accountCodeKey]);
        return Admin::service('ForgetService')
            ->with('password', $request->post('password'))
            ->with('password_confirmation', $request->post('password_confirmation'))
            ->with('account', $account)
            ->with('account_code_key', $accountCodeKey)
            ->with('account_code', $request->post('account_code'))
            ->run('accountReset');
    }

}
