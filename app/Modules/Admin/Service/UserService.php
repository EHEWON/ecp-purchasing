<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\UserRepo,
    Middleware\UserMiddleware
};
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;

class UserService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        UserMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
    ];
    public $beforeEvent = [];
    public $afterEvent = [
    ];

    public function getRules() {
        return [
            'change' => [
                'user_id' => 'required',
                'password' => 'required|confirmed|min:6'
            ],
        ];
    }

    public function getMessages() {
        return [
            'change' => [
                'user_id.required' => Lang::get('user.enter_user_id'),
                'password.required' => Lang::get('user.enter_new_password'),
                'password.confirmed' => Lang::get('user.new_password_invalid'),
                'password.min' => Lang::get('user.password_length_min')
            ],
        ];
    }

    protected $model;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new UserRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new UserRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new UserRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new UserRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new UserRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new UserRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new UserRepo)->deleteData($request);
    }

//    public function import(Request $request) {
//        return (new UserRepo)->import($request);
//    }
//
    public function export(Request $request) {
        return (new UserRepo)->export($request);
    }

    public function pinyin(Request $request) {
        if (empty($request->name)) {
            check(false, '请输入需要获取拼音的文字');
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        return $pinyin->permalink($request->name, '');
    }

    /**
     * 新增人员类型
     * @return
     */
    public function orgs(Request $request) {
        return (new UserRepo)->orgs($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function roles(Request $request) {
        return (new UserRepo)->roleOrgs($request);
    }

    /**
     * 获取登录人员组织
     * @return
     */
    public function menus(Request $request) {
        return (new UserRepo)->menusTree($request);
    }

    public function getUserByRole(Request $request) {
        return (new UserRepo)->getUserByRole($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function orgTree(Request $request) {
        return (new UserRepo)->orgTree($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function orgList(Request $request) {
        return (new UserRepo)->orgList($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function change(Request $request) {
        return (new UserRepo)->change($request);
    }

    /**
     * 获取业务员
     * @return
     */
    public function persons(Request $request) {
        return (new UserRepo)->persons($request);
    }

}
