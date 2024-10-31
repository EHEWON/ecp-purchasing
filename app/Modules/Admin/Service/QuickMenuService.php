<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Common\Models\{
    QuickMenus,
    UserSupplier
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Redis
};

/**
 * 快捷菜单
 */
class QuickMenuService extends Service {

    public $middleware = [
    ];
    public $beforeEvent = [];
    public $afterEvent = [];
    protected $purchaserId = '';
    protected $supplierId = '';
    protected $admin = null;

    public function getRules() {
        return [
            'list' => [
            ]
        ];
    }

    public function getMessages() {
        return [
            'list' => [
            ]
        ];
    }

    protected $model;

    public function __construct() {
        parent::__construct();
        $this->model = new QuickMenus();
        $this->admin = Auth::guard('admin')->user();
    }

    public function getPPurchaserId() {
        if (!empty($this->purchaserId)) {
            return $this->purchaserId;
        }
        $authorization = Auth::guard('admin')->getToken();
        if (empty($authorization)) {
            return;
        }
        $redisKey = md5($authorization);
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $this->purchaserId = Redis::get($redisKey);
        }
        return $this->purchaserId;
    }

    public function getPSupplierId() {
        if (!empty($this->supplierId)) {
            return $this->supplierId;
        }
        $authorization = Auth::guard('admin')->getToken();
        if (empty($authorization)) {
            return;
        }
        $redisKey = md5($authorization) . ':supplier_id';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $this->supplierId = Redis::get($redisKey);
            if (!empty($this->supplierId)) {
                return $this->supplierId;
            }
        }
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $this->supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        Redis::set($redisKey, $this->supplierId, 86400);
        return $this->supplierId;
    }

    /**
     * 询单列表数据
     * @param  Request $request
     * @return array
     */
    public function handle(Request $request) {
        switch ($this->admin->user_type) {
            case 'SUPPLIER':
                $teamId = $this->getPSupplierId();
                break;
            default:
                $teamId = $this->getPPurchaserId();
                break;
        }

        $admin = Auth::guard('admin')->user();
        return QuickMenus::select('id', 'name', 'url', 'icon', 'title')
                        ->where('created_by', $admin->user_id)
                        ->where('team_id', $teamId)
                        ->where('menu_type', $admin->user_type)
                        ->get()->toArray();
    }

    /**
     * 新增
     *
     * @return array
     */
    public function add(Request $request) {
        $params = $request->post();
        $menus = $params['menus'] ?? [];
        $admin = Auth::guard('admin')->user();
        switch ($this->admin->user_type) {
            case 'SUPPLIER':
                $teamId = $this->getPSupplierId();
                break;
            default:
                $teamId = $this->getPPurchaserId();
                break;
        }
        $userId = $admin->user_id;
        QuickMenus::where('created_by', $userId)
                ->where('team_id', $teamId)
                ->delete();
        if (empty($menus)) {
            return [];
        }
        $dataList = [];
        foreach ($menus as $menu) {
            if (empty($menu['url']) || empty($menu['name'])) {
                continue;
            }
            $dataList[] = [
                'menu_type' => $admin->user_type,
                'team_id' => $teamId,
                'url' => $menu['url'],
                'icon' => $menu['icon'],
                'title' => $menu['title'],
                'name' => $menu['name'],
                'created_by' => $userId,
            ];
        }
        QuickMenus::insert($dataList);
        return QuickMenus::select('id', 'name', 'url', 'icon', 'title')
                        ->where('created_by', $userId)
                        ->where('menu_type', $admin->user_type)
                        ->where('team_id', $teamId)
                        ->get()
                        ->toArray();
    }

    /**
     * 删除
     * @param  Request $request
     * @return
     */
    public function deleteData(Request $request) {
        $id = $request->post('id');
        $admin = Auth::guard('admin')->user();
        $user_id = $admin->user_id;
        QuickMenus::where('id', $id)
                ->where('created_by', $user_id)
                ->where('menu_type', $admin->user_type)
                ->delete();
    }

}
