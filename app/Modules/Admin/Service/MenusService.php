<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\MenusRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Redis
};

class MenusService extends Service {

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
        return (new MenusRepo)->getList($request);
    }

    /**
     * 树状菜单列表
     * @param Request $request
     */
    public function getTreeList(Request $request) {
        return (new MenusRepo)->getTreeList($request);
    }

    /**
     * 详情
     * @param Request $request
     */
    public function info($id) {
        return (new MenusRepo)->info($id);
    }

    /**
     * 新增
     * @param Request $request
     */
    public function add(Request $request) {
        return (new MenusRepo)->addData($request);
    }

    /**
     * 启用
     * @param Request $request
     */
    public function enable(Request $request) {
        return (new MenusRepo)->enable($request);
    }

    /**
     * 禁用
     * @param Request $request
     */
    public function disable(Request $request) {
        return (new MenusRepo)->disable($request);
    }

    /**
     * 删除
     * @param Request $request
     */
    public function delete(Request $request) {
        return (new MenusRepo)->deleteData($request);
    }

    /**
     * 修改
     * @param Request $request
     */
    public function edited($id, Request $request) {
        return (new MenusRepo)->edited($id, $request);
    }

    public function userTree() {
        $token1 = Auth::guard('admin')->getToken();
        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            $data['ret'] = false;
            $data['message'] = '管理员数据无法查询';
            return $data;
        }
        $data['user_type'] = $admin['user_type'];
        $data['user_id'] = $admin['user_id'];
        if ($data['user_type'] == 'SUPPLIER') {

            if (!empty($token1) && Redis::command('exists', ['cur_pid' . $token1])) {
                $cur_pid = Redis::get('cur_pid' . $token1);
            } else {
                $data['ret'] = false;
                $data['message'] = 'token过期';
                return $data;
            }
        } else {
            $redisKey = md5($token1);
            if (!empty($token1) && Redis::command('exists', [$redisKey])) {
                $cur_pid = Redis::get($redisKey);
            } else {
                $data['ret'] = false;
                $data['message'] = 'token过期';
                return $data;
            }
            $data['cur_pid'] = $cur_pid;
        }
        $menus_data = [];
        $menus = new MenusRepo;
        return $menus->getMenusByUser($admin['user_type'], $menus_data);
    }

    public function userMenus() {
        $token1 = Auth::guard('admin')->getToken();
        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            $data['ret'] = false;
            $data['message'] = '管理员数据无法查询';
            return $data;
        }
        $data['user_type'] = $admin['user_type'];
        $data['user_id'] = $admin['user_id'];
        if ($data['user_type'] == 'SUPPLIER') {

            if (!empty($token1) && Redis::command('exists', ['cur_pid' . $token1])) {
                $team_id = Redis::get('cur_pid' . $token1);
            } else {
                $data['ret'] = false;
                $data['message'] = 'token过期';
                return $data;
            }
        } else {
            $redisKey = md5($token1);
            if (!empty($token1) && Redis::command('exists', [$redisKey])) {
                $team_id = Redis::get($redisKey);
            } else {
                $data['ret'] = false;
                $data['message'] = 'token过期';
                return $data;
            }
            $data['cur_pid'] = $team_id;
        }
        $menus_data = [];
        $userId = $admin['user_id'];
        $role_group = $admin['user_type'];
        $menusObj = DB::table('role_user', 'ru')
                ->selectRaw('rm.menu_id')
                ->join('roles as r', 'ru.role_id', '=', 'r.id', 'left')
                ->join('role_has_menus as rm', 'rm.role_id', '=', 'ru.role_id', 'left')
                ->where('user_id', $userId)
                ->where('ru.team_id', $team_id)
                ->where('ru.role_group', $role_group)
                ->whereNotNull('rm.menu_id')
                ->where('r.deleted_flag', 'N')
                ->where('rm.deleted_flag', 'N')
                ->groupBy('rm.menu_id')
                //->toSql();
                ->get();
        $data_arr = $menusObj->toArray();
        $menu_ids = array_column($menusObj->toArray(), 'menu_id');
        if (empty($menu_ids)) {
            return [];
        }
        $query = DB::table('menus')->selectRaw('menus.*,perm.route,perm.route,method')
                        ->join('permissions as perm', 'perm.id', '=', 'menus.perm_id', 'left')->where('menus.deleted_flag', 'N')->where('menus.status', 'NORMAL')->wherein('menus.id', $menu_ids);
        if (!empty($menus_data['type'])) {
            $query->where('menus.type', $menus_data['type']);
        }
        if (!empty($menus_data['path'])) {
            $num = $this->model->where('path', $menus_data['path'])->where('deleted_flag', 'N')->count();
            if ($num > 1) {
                return check(false, 'path不唯一', 0);
            }
            if ($num < 1) {
                return check(false, 'path不存在', 0);
            }
            $info = $this->model->where('path', $menus_data['path'])->where('deleted_flag', 'N')->first();
            $menus_data['pid'] = !empty($info['id']) ? $info['id'] : null;
        }
        if (!empty($menus_data['pid'])) {
            $query->where('menus.parent_id', $menus_data['pid']);
        } else {
            $menus_data['pid'] = 0;
        }
        $menu_data = $query->orderBy('menus.parent_id', 'asc')
                ->orderBy('menus.sort', 'desc')
                ->get()
                ->toArray();
        if ($menu_data) {
            return $menu_data;
        } else {
            return [];
        }
    }

}
