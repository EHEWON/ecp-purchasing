<?php

namespace App\Modules\Admin\Repository;

use Illuminate\Http\Request;
use App\Common\Contracts\Repository;
use App\Common\Models\Menus;
use Illuminate\Support\Facades\Auth;

class MenusRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Menus();
        parent::__construct($this->model);
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('display_name', 'like', '%' . $keyword . '%')
                        ->orWhere('id', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->parent_id) || $request->parent_id === "0" || $request->parent_id === 0) {
            $parent_id = $request->parent_id;
            $parent_ids = is_array($parent_id) ? $parent_id : explode(',', trim($parent_id));
            $query->whereIn('parent_id', $parent_ids);
        }
        if (!empty($request->perm_id)) {
            $perm_id = $request->perm_id;
            $perm_ids = is_array($perm_id) ? $perm_id : explode(',', trim($perm_id));
            $query->whereIn('perm_id', $perm_ids);
        }
        if (!empty($request->type)) {
            $query->where('type', $request->type);
        }
        if (!empty($request->menu_type)) {
            if ($request->menu_type == 'PLATFORM') {
                $query->whereIn('menu_type', [$request->menu_type, 'COMMON', 'PURCHASER']);
            } else {
                $query->whereIn('menu_type', [$request->menu_type, 'COMMON']);
            }
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('created_at', $createAts);
        } else if (!empty($request->createtime)) {
            $createtime = trims($request->createtime);
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('created_at', $createAts);
        }
    }

    public function getList(Request $request, $filed = '*') {
        $query = $this->model->selectRaw($filed);
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $query->orderBy('sort', 'asc');
        $object = $query->orderBy('created_at', 'desc')->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getMenusByUser($roleGroup = 'purchaser', $menus_data = []) {

        $query = $this->model->selectRaw('menus.*,perm.route,perm.route,method')
                ->join('permissions as perm', 'perm.id', '=', 'menus.perm_id', 'left')
                ->where('menus.deleted_flag', 'N')
                ->where('menus.status', 'NORMAL')
                ->where('menus.menu_type', $roleGroup);
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
            return filterMenus($menu_data, $menus_data['pid']);
        } else {
            return [];
        }
    }

    public function getTreeList(Request $request, $filed = '*') {
        $query = $this->model->selectRaw($filed);
        $this->getWhere($query, $request);
        $object = $query->where('deleted_flag', 'N')
                ->orderBy('parent_id', 'asc')
                ->orderBy('sort', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        if (empty($request->parent_id)) {
            $request->parent_id = 0;
        }
        $data = filterMenus($list, $request->parent_id);
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    public function Info(int $id) {
        return $this->model
                        ->selectRaw('menus.*,perm.route,method')
                        ->join('permissions as perm', 'perm.id', '=', 'menus.perm_id', 'left')
                        ->where('menus.id', $id)
                        ->first();
    }

    public function addData(Request $request) {
        $admin = Auth::guard('admin')->user();
        $data = [
            'parent_id' => empty($request->parent_id) ? 0 : $request->parent_id,
            'parent_tree' => empty($request->parent_tree) ? 0 : $request->parent_tree,
            'perm_id' => empty($request->perm_id) ? 0 : $request->perm_id,
            'perm_tree' => empty($request->perm_tree) ? 0 : $request->perm_tree,
            'menu_type' => $request->menu_type,
            'display_name' => $request->display_name,
            'name' => $request->name,
            'icon' => !empty($request->icon) ? $request->icon : '',
            'path' => !empty($request->path) ? $request->path : '',
            'type' => !empty($request->type) ? $request->type : '',
            'weight' => !empty($request->weight) ? $request->weight : 0,
            'status' => !empty($request->status) ? $request->status : 'NORMAL',
            'sort' => intval($request->sort),
            'platform' => !empty($request->platform) ? $request->platform : '',
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'deleted_flag' => !empty($request->deleted_flag) ? $request->deleted_flag : 'N',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return $this->model->insertGetId($data);
    }

    /**
     * 启用
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        $findPids = Menus::selectRaw('parent_id')->whereIn('id', $ids)->get()->toArray();
        $menu_ids = array_column($findPids, 'parent_id');
        $cheak = Menus::whereIn('id', $menu_ids)
                ->where('status', "DISABLED")
                ->get()
                ->toArray();
        if (empty($cheak)) {
            return Menus::whereIn('id', $ids)->update([
                        'status' => "NORMAL",
                        'updated_by' => $admin->user_id,
                        'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            return check(false, '提示先开启父级', 0);
        }
    }

    /**
     * 禁用
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();

        $ids = $request->ids;
        $cheak = Menus::whereIn('parent_id', $ids)
                ->where('status', "NORMAL")
                ->get()
                ->toArray();
        if (empty($cheak)) {
            return Menus::whereIn('id', $ids)->update([
                        'status' => "DISABLED",
                        'updated_by' => $admin->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            return check(false, '先关闭全部子级，在关闭父级', 0);
        }
    }

    /**
     * 删除
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        $cheak = Menus::whereIn('parent_id', $ids)
                ->where('deleted_flag', "N")
                ->get()
                ->toArray();
        if (empty($cheak)) {
            return Menus::whereIn('id', $ids)->update([
                        'deleted_flag' => "Y",
                        'updated_by' => $admin->user_id,
                        'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            return check(false, '先删除全部子级，在删除父级', 0);
        }
    }

    /**
     * 删除
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function edited($id, Request $request) {
        $query = $this->model->selectRaw('*');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            check(false, '菜单不存在');
        }
        $admin = Auth::guard('admin')->user();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $admin->user_id;
        if ($request->status) {
            $data['status'] = $request->status;
        }
        if ($request->parent_id) {
            $data['parent_id'] = $request->parent_id;
        }
        if ($request->name) {
            $data['name'] = $request->name;
        }
        if ($request->display_name) {
            $data['display_name'] = $request->display_name;
        }
        $data['icon'] = $request->icon;
        if ($request->path) {
            $data['path'] = $request->path;
        }
        if ($request->type) {
            $data['type'] = $request->type;
        }
        if ($request->weight) {
            $data['weight'] = $request->weight;
        }
        if ($request->sort) {
            $data['sort'] = $request->sort;
        }
        if ($request->platform) {
            $data['platform'] = $request->platform;
        }
        if ($request->deleted_flag) {
            $data['deleted_flag'] = $request->deleted_flag;
        }
        if ($request->parent_tree) {
            $data['parent_tree'] = $request->parent_tree;
        }
        if ($request->perm_id) {
            $data['perm_id'] = $request->perm_id;
        }
        if ($request->perm_tree) {
            $data['perm_tree'] = $request->perm_tree;
        }
        if ($request->menu_type) {
            $data['menu_type'] = $request->menu_type;
        }
        return Menus::where('id', $id)->update($data);
    }

}
