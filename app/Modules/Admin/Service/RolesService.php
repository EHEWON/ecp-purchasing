<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\RolesRepo;
use Illuminate\Http\Request;

class RolesService extends Service {

    protected $guard = 'admin';
    public $middleware = [
    ];
    public $beforeEvent = [];
    public $afterEvent = [
    ];

    public function getRules() {
        return [
        ];
    }

    public function getMessages() {
        return [
        ];
    }
    protected $model;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 菜单列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new RolesRepo)->getList($request);
    }
    public function getRoleByCompany(Request $request) {
        return (new RolesRepo)->getRoleByCompany($request);
    }
    /**
     * 树状菜单列表
     * @param Request $request
     */
    public function getTreeList(Request $request) {
        return (new RolesRepo)->getTreeList($request);
    }
    
    /**
     * 详情
     * @param Request $request
     */
    public function info($id) {
        return (new RolesRepo)->info($id);
    }
    
     /**
     * 新增
     * @param Request $request
     */
    public function add(Request $request) {
        return (new RolesRepo)->addData($request);
    }
    
     /**
     * 启用
     * @param Request $request
     */
    public function enable(Request $request) {
         return (new RolesRepo)->enable($request);
    }
    
    /**
     * 禁用
     * @param Request $request
     */
    public function disable(Request $request) {
        return (new RolesRepo)->disable($request);
    }
    
    /**
     * 删除
     * @param Request $request
     */
    public function delete(Request $request) {
        return (new RolesRepo)->deleteData($request);
    }
    
    /**
     * 修改
     * @param Request $request
     */
    public function edited(Request $request) {
        return (new RolesRepo)->edited($request->id,$request);
    }
    
    public function hasMenus($id,Request $request) {
        return (new RolesRepo)->hasMenus($id,$request);
    }
    
    public function menusList($id) {
        return (new RolesRepo)->menusList($id);
    }
    
    public function listbyuser($id,Request $request) {
        return (new RolesRepo)->listbyuser($id,$request);
    }
    
     public function userHasRoles($id,Request $request) {
        return (new RolesRepo)->userHasRoles($id,$request);
    }
    
}
