<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class AuthController extends Controller {

    public function getRules() {
        return [
            'login' => [
                'username' => 'required',
                'password' => 'required'
            ]
        ];
    }

    /**
     * 登录流程
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\Admin\AuthException
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function login(Request $request) {
        return Admin::service('AuthService')
            ->with('username', $request->post('username'))
            ->with('password', $request->post('password'))
            ->run('login');
    }
    public function bossLogin(Request $request) {
        return Admin::service('AuthService')
            ->with('username', $request->post('username'))
            ->run('bossLogin');
    }
    /**
     * 获取登录管理员信息
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\Admin\AuthException
     */
    public function me(Request $request) {
        return Admin::service('AuthService')->me($request);
    }

    /**
     * 登录用户信息
     * @return 
     */
    public function info(Request $request) {
        return Admin::service('AuthService')->info($request);
    }

    /**
     * 重置密码
     * @return 
     */
    public function change(Request $request) {
        return Admin::service('AuthService')->pass($request->post())->run('change');
    }

    /**
     * 修改头像
     * @return 
     */
    public function avatar(Request $request) {
        return Admin::service('AuthService')->pass($request->post())->run('avatar');
    }

    /**
     * 退出登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        return Admin::service('AuthService')->run('logout');
    }

    public function getRabcList() {
        return Admin::service('AuthService')->run('getRabcList');
    }

    /**
     * 重置密码
     * @return 
     */
    public function changeOrg(Request $request) {
        return Admin::service('AuthService')->changeOrg($request);
    }

    public function purchasers() {
        return Admin::service('AuthService')->purchasers();
    }

}
