<?php

namespace App\Modules\Admin\Repository;

use Illuminate\Http\Request;
use App\Common\Contracts\Repository;
use App\Common\Models\Permissions;
use Illuminate\Support\Facades\{
    Auth,
    DB
};

class PermissionsRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Permissions();
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
        if (!empty($request->parent_id) || $request->parent_id === "0") {
            $parent_id = $request->parent_id;
            $parent_ids = is_array($parent_id) ? $parent_id : explode(',', trim($parent_id));
            $query->whereIn('parent_id', $parent_ids);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        if (!empty($request->permission_type)) {
            $query->where('permission_type', $request->permission_type);
        }
        if (!empty($request->type)) {
            $query->where('type', $request->type);
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
        $object = $query->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->get(); //tosql();
        if (empty($object)) {
            return ['data' => []];
        }
        $data = $object->toArray();
        $list = [];
        $list['data'] = $data;
        return $list;
    }

    public function getTreeList(Request $request, $filed = '*') {
        $query = $this->model->selectRaw("*");
        $this->getWhere($query, $request);
        $object = $query->where('deleted_flag', 'N')->where('status', 'NORMAL')->orderBy('parent_id', 'asc')->orderBy('sort', 'desc')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        if (empty($request->parent_id)) {
            $request->parent_id = 0;
        }
        $data = filterMenus($list, $request->parent_id);
        return $data;
    }

    public function Info(int $id) {
        return $this->model
                        ->where('id', $id)
                        ->first();
    }

    public function addData(Request $request) {
        $admin = Auth::guard('admin')->user();
        $data = [
            'parent_id' => empty($request->parent_id) ? 0 : $request->parent_id,
            'parent_tree' => empty($request->parent_tree) ? 0 : $request->parent_tree,
            'display_name' => $request->display_name,
            'name' => $request->name,
            'route' => !empty($request->route) ? $request->route : '',
            'method' => !empty($request->method) ? $request->method : '',
            'type' => !empty($request->type) ? $request->type : '',
            'weight' => !empty($request->weight) ? $request->weight : 0,
            'sort' => intval($request->sort),
            'is_share' => !empty($request->is_share) ? $request->is_share : '',
            'status' => !empty($request->status) ? $request->status : 'NORMAL',
            'shortcut_flag' => !empty($request->shortcut_flag) ? $request->shortcut_flag : '',
            'created_by' => $admin->user_id,
            'updated_by' => $admin->user_id,
            'deleted_flag' => !empty($request->deleted_flag) ? $request->deleted_flag : 'N',
            'permission_type' => !empty($request->permission_type) ? $request->permission_type : 'PURCHASER',
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
        $findPids = Permissions::selectRaw('parent_id')->whereIn('id', $ids)->get()->toArray();
        $perm_ids = array_column($findPids, 'parent_id');
        $cheak = Permissions::whereIn('id', $perm_ids)
                ->where('status', "DISABLED")
                ->get()
                ->toArray();
        if (empty($cheak)) {
            return Permissions::whereIn('id', $ids)->update([
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
        $cheak = Permissions::whereIn('parent_id', $ids)
                ->where('status', "NORMAL")
                ->get()
                ->toArray();
        if (empty($cheak)) {
            return Permissions::whereIn('id', $ids)->update([
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
        $cheak = Permissions::whereIn('parent_id', $ids)
                ->where('deleted_flag', "N")
                ->get()
                ->toArray();
        if (empty($cheak)) {
            return Permissions::whereIn('id', $ids)->update([
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
        if ($request->route) {
            $data['route'] = $request->route;
        }
        if ($request->method) {
            $data['method'] = $request->method;
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
        if ($request->is_share) {
            $data['is_share'] = $request->is_share;
        }
        if ($request->deleted_flag) {
            $data['deleted_flag'] = $request->deleted_flag;
        }
        if ($request->parent_tree) {
            $data['parent_tree'] = $request->parent_tree;
        }
        if ($request->shortcut_flag) {
            $data['shortcut_flag'] = $request->shortcut_flag;
        }
        if ($request->permission_type) {
            $data['permission_type'] = $request->permission_type;
        }
        return Permissions::where('id', $id)->update($data);
    }

    public function getPermsByRolesIds($role_ids) {
        if (empty($role_ids)) {
            return [];
        }
        $permsObj = DB::table('permissions', 'perm')
                ->selectRaw('perm.id,LOWER(CONCAT_WS(":",perm.method,scope)) AS route')
                ->join('role_has_permissions as rhp', 'rhp.perm_id', '=', 'perm.id', 'left')
                ->where(function($q)use($role_ids) {
                    $q->whereIn('rhp.role_id', $role_ids)
                    ->orWhere('is_share', 'N');
                })
                ->where('status', 'NORMAL')
                ->where('perm.deleted_flag', 'N')
                ->groupBy('perm.id')
                ->get();
        $perm_routes = array_column($permsObj->toArray(), 'route');
        if ($perm_routes) {
            return $perm_routes;
        } else {
            return [];
        }
    }

}
