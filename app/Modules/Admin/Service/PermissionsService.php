<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\PermissionsRepo;
use Illuminate\Http\Request;

class PermissionsService extends Service {

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
        return (new PermissionsRepo)->getList($request);
    }
    
    /**
     * 树状菜单列表
     * @param Request $request
     */
    public function getTreeList(Request $request) {
        return (new PermissionsRepo)->getTreeList($request);
    }
    
    /**
     * 详情
     * @param Request $request
     */
    public function info($id) {
        return (new PermissionsRepo)->info($id);
    }
    
     /**
     * 新增
     * @param Request $request
     */
    public function add(Request $request) {
        return (new PermissionsRepo)->addData($request);
    }
    
     /**
     * 启用
     * @param Request $request
     */
    public function enable(Request $request) {
         return (new PermissionsRepo)->enable($request);
    }
    
    /**
     * 禁用
     * @param Request $request
     */
    public function disable(Request $request) {
        return (new PermissionsRepo)->disable($request);
    }
    
    /**
     * 删除
     * @param Request $request
     */
    public function delete(Request $request) {
        return (new PermissionsRepo)->deleteData($request);
    }
    
    /**
     * 修改
     * @param Request $request
     */
    public function edited($id,Request $request) {
        return (new PermissionsRepo)->edited($id,$request);
    }
    
    
}
