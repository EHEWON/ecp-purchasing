<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Menus,
    RoleHasMenus,
    RoleHasPermissions,
    RoleUsers,
    Roles
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB
};

class RolesRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Roles();
        parent::__construct($this->model);
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {

        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('display_name', 'like', '%' . $keyword . '%')
                        ->orWhere('role_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->role_group)) {
            $query->where('role_group', $request->role_group);
        }
        if (!empty($request->role_groups)) {
            $roleGroups = explode(',', $request->role_groups);
            $query->where('role_group', $roleGroups);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        if (!empty($request->team_id)) {
            $query->where('team_id', $request->team_id);
        }
        if (!empty($request->created_by)) {
            $query->where('created_by', $request->created_by);
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
        $object = $query->where('deleted_flag', 'N')
                ->orderBy('sort', 'asc')
                ->orderBy('created_at', 'desc')
                ->get(); //tosql();
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            if ($data[$i]['type'] == 'SYSTEM') {
                $data[$i]['type_name'] = '共享';
            } else {
                $data[$i]['type_name'] = '不共享';
            }
            if ($data[$i]['role_group'] == 'PURCHASER' || $data[$i]['role_group'] == 'PLATFORM') {
                (new OrgRepo)->setOrg($data[$i], 'team_id', 'team_name');
            } else {
                (new SupplierRepo)->setSupplier($data[$i], 'team_id', 'team_name', true);
            }
            if ($data[$i]['team_name'] == '') {
                (new OrgRepo)->setOrg($data[$i], 'team_id', 'team_name');
            }
        }
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function Info(int $id) {
        $data = $this->model
                ->where('id', $id)
                ->first()
                ->toArray();
        $data['team_id'] = (string) $data['team_id'];
        (new UserRepo)->setUser($data, 'created_by', 'created_name');
        (new UserRepo)->setUser($data, 'updated_by', 'updated_name');
        if ($data['role_group'] == 'PURCHASER' || $data['role_group'] == 'PLATFORM') {
            (new OrgRepo)->setOrg($data, 'team_id', 'team_name');
        } else {
            (new SupplierRepo)->setSupplier($data, 'team_id', 'team_name', true);
        }
        return $data;
    }

    public function addData(Request $request) {
        $admin = Auth::guard('admin')->user();
        $data = [
            'team_id' => empty($request->team_id) ? 0 : $request->team_id,
            'display_name' => $request->display_name,
            'type' => $request->type,
            'name' => $request->name,
            'role_no' => !empty($request->role_no) ? $request->role_no : '',
            'role_group' => !empty($request->role_group) ? $request->role_group : '',
            'remarks' => !empty($request->remarks) ? $request->remarks : '',
            'status' => !empty($request->status) ? $request->status : 'NORMAL',
            'sort' => intval($request->sort),
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
        return Roles::whereIn('id', $ids)->update([
                    'status' => "NORMAL",
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
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
        return Roles::whereIn('id', $ids)->update([
                    'status' => "DISABLED",
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s')
        ]);
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
        foreach ($ids as $id) {
            $data = $this->model
                    ->where('id', $ids)
                    ->first()
                    ->toArray();
            if ($data['type'] == 'SYSTEM') {
                check(false, '共享角色无法删除');
            }
        }
        return Roles::whereIn('id', $ids)->whereNotIn('role_group', ['admin'])->update([
                    'deleted_flag' => "Y",
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 修改
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
        $purchaserId = $this->getPPurchaserId();
        if ($object->type === 'SYSTEM' && $object->team_id !== $purchaserId) {
            check(false, '请不要修改系统共享角色');
        }
        $admin = Auth::guard('admin')->user();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $admin->user_id;
        $data['type'] = $request->type;
        if (!empty($request->team_id)) {
            $data['team_id'] = $request->team_id;
        }
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
        if ($request->icon) {
            $data['icon'] = $request->icon;
        }
        if ($request->path) {
            $data['path'] = $request->path;
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
        if ($request->role_group) {
            $data['role_group'] = $request->role_group;
        }
        if ($request->deleted_flag) {
            $data['deleted_flag'] = $request->deleted_flag;
        }
        if ($request->perm_id) {
            $data['perm_id'] = $request->perm_id;
        }
        if ($request->role_no) {
            $data['role_no'] = $request->role_no;
        }
        return Roles::where('id', $id)->update($data);
    }

    /**
     * 获取角色菜单
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function listbyuser($id, Request $request) {
        $team_id = $request->team_id;
        if (empty($team_id)) {
            check(false, '所述业务单元id不能为空');
        }
        $role_group = $request->role_group;
        if (empty($role_group)) {
            check(false, '所述业务单元类型不能为空');
        }
        $menusObj = DB::table('roles', 'r')
                ->selectRaw('r.*')
                ->join('role_user as ru', 'ru.role_id', '=', 'r.id', 'left')
                ->where('ru.user_id', $id)
                ->where('ru.team_id', $team_id)
                ->where('ru.role_group', $role_group)
                ->where('r.deleted_flag', 'N')
                ->get();
        $data = $menusObj->toArray();
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    /**
     * 获取角色菜单
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function menusList($id) {
        $menusObj = DB::table('menus', 'm')
                ->selectRaw('m.*,rm.scope')
                ->join('role_has_menus as rm', 'rm.menu_id', '=', 'm.id', 'left')
                ->where('rm.role_id', $id)
                ->whereNotNull('rm.menu_id')
                ->where('m.deleted_flag', 'N')
                ->where('rm.deleted_flag', 'N')
                ->groupBy('rm.menu_id')
                ->get();
        $data = $menusObj->toArray();
        //$menu_ids = array_column($menusObj->toArray(), 'menu_id');
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    /**
     * 授权
     * @param int $id
     * @param Request $request
     * @return array
     */
    public function hasMenus($id, Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($request->data)) {
            check(false, '菜单不能为空');
        }
        if (empty($id)) {
            check(false, '角色不能为空');
        }
        RoleHasMenus::where('role_id', $id)->update([
            'deleted_flag' => "Y",
            'updated_by' => $admin->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        RoleHasPermissions::where('role_id', $id)->update([
            'deleted_flag' => "Y",
            'updated_by' => $admin->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);


        $menuIds = array_column($request->data, 'menu_id');
        $scopeArr = array_column($request->data, 'scope', 'menu_id');
        if (empty($menuIds)) {
            check(false, '菜单不能为空');
        }
        $list = Menus::whereIn('id', $menuIds)
                ->where('deleted_flag', 'N')
                ->where('status', 'NORMAL')
                ->selectRaw('perm_id,id AS menu_id')
                ->get()
                ->toArray();
        $roleMenuList = [];
        $rolePermissionList = [];
        foreach ($list as $v) {
            if (empty($scopeArr[$v['menu_id']])) {
                continue;
            }
            $scope = $scopeArr[$v['menu_id']];
            $roleMenuList[] = [
                'role_id' => $id,
                'menu_id' => $v['menu_id'],
                'scope' => $scope,
                'deleted_flag' => "N",
                'created_by' => $admin->user_id,
                'updated_by' => $admin->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (empty($v['perm_id'])) {
                continue;
            }
            $rolePermissionList[] = ['role_id' => $id,
                'menu_id' => $v['menu_id'],
                'perm_id' => $v['perm_id'],
                'scope' => $scope,
                'deleted_flag' => "N",
                'created_by' => $admin->user_id,
                'updated_by' => $admin->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),];
        }
        !empty($roleMenuList) ? RoleHasMenus::upsert($roleMenuList, [
                            'menu_id',
                            'perm_id',
                                ], [
                            'scope',
                            'deleted_flag',
                            'updated_by',
                            'updated_at'
                                ]
                        ) : null;
        !empty($rolePermissionList) ? RoleHasPermissions::upsert($rolePermissionList, [
                            'perm_id', 'role_id', 'menu_id'
                                ], [
                            'scope',
                            'deleted_flag',
                            'updated_by',
                            'updated_at'
                        ]) : null;
        return true;
    }

    /**
     * 用户角色
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function userHasRoles($id, Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($id)) {
            check(false, '用户不能为空');
        }
        if (empty($request->team_ids)) {
            check(false, '采购商ID或供应商ID不能为空');
        }
        if (empty($request->role_group)) {
            check(false, '类型不能为空');
        }
        foreach ($request->team_ids as $team_id) {
            RoleUsers::where('user_id', $id)
                    ->where('team_id', $team_id)
                    ->where('role_group', $request->role_group)
                    ->update([
                        'deleted_flag' => "Y",
                        'updated_by' => $admin->user_id,
                        'updated_at' => date('Y-m-d H:i:s'),
            ]);
            foreach ($request->role_ids as $k) {
                if (empty($k)) {
                    check(false, '角色不能为空');
                }
                $roleUser = RoleUsers::where('user_id', $id)
                        ->where('role_id', $k)
                        ->where('team_id', $team_id)
                        ->where('role_group', $request->role_group)
                        ->first();
                if ($roleUser) {
                    RoleUsers::where('role_id', $k)
                            ->where('team_id', $team_id)
                            ->where('role_group', $request->role_group)
                            ->where('user_id', $id)
                            ->update([
                                'deleted_flag' => "N",
                                'updated_by' => $admin->user_id,
                                'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $perm_info = [
                        'role_id' => $k,
                        'user_id' => $id,
                        'team_id' => $team_id,
                        'role_group' => $request->role_group,
                        'deleted_flag' => "N",
                        'created_by' => $admin->user_id,
                        'updated_by' => $admin->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    RoleUsers::insertGetId($perm_info);
                }
            }
        }
        return true;
    }

    public function getRolesByUser($userType = 'purchaser') {

        $roleGroups = $this->getRoleGroups($userType);
        $menusObj = DB::table('role_user', 'ru')
                ->selectRaw('r.id')
                ->join('roles as r', 'ru.role_id', '=', 'r.id', 'left')
                ->whereIn('ru.role_group', $roleGroups)
                ->where('r.deleted_flag', 'N')
                ->groupBy('r.id')
                ->get();
        $rolesIds = array_column($menusObj->toArray(), 'id');
        if (empty($rolesIds)) {
            return [];
        }
        return $rolesIds;
    }

    public function getRoleGroups($userType) {
        switch (strtoupper($userType)) {
            case 'PLATFORM':
            case 'PURCHASER':
            case 'ORG':
                return ['PURCHASER', 'COMMON', 'SYSTEM'];
            case 'SUPPLIER':
                return ['SUPPLIER', 'COMMON', 'SYSTEM'];
        }
    }

    public function getRoleByCompany(Request $request) {
        $query = $this->model;
        if (!empty($request->role_group)) {
            $query = $query->where('role_group', $request->role_group);
        } else {
            check(false, '类型不能为空');
        }
        if (!empty($request->keyword)) {
            $query = $query->where('name', 'like', '%' . trim($request->keyword) . '%');
        }

        if (empty($request->team_id)) {
            check(false, '公司id不能为空');
        }

        $object = $query->where('deleted_flag', 'N')
                ->where('status', 'NORMAL')
                ->whereNot('role_no', 'GYS002')//过滤临时供应商
                ->orderBy('sort', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        if (empty($object)) {
            return [];
        }
        return $object;
    }

}
