<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    RoleUsers,
    Roles
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolesUserRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new RoleUsers();
        parent::__construct($this->model);
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function updateOrAdd(Request $request) {
        $dataList = [];
        $list = $request->all();
        $userId = $request->post('user_id');
        RoleUsers::where('user_id', $userId)->delete();
//        $userType = User::where('user_id', $userId)->value('user_type');

        $admin = Auth::guard('admin')->user();
        foreach ($list['items'] as $item) {
            if (empty($item['role_ids'])) {
                continue;
            }
            foreach ($item['role_ids'] as $roleId) {
                $dataList[] = [
                    'role_group' => !empty($item['role_group']) ? trim($item['role_group']) : 'PURCHASER',
                    'role_id' => intval($roleId),
                    'content_id' => intval($item['team_id']),
                    'team_id' => intval($item['team_id']),
                    'user_id' => intval($userId),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_by' => $admin->user_id,
                    'updated_by' => $admin->user_id,
                    'deleted_flag' => 'N',
                ];
            }
        }
        if (empty($dataList)) {
            return true;
        }
        return RoleUsers::upsert($dataList, ['role_id', 'team_id', 'user_id'], ['role_id', 'team_id', 'content_id', 'user_id', 'deleted_flag', 'updated_at', 'updated_by', 'role_group']);
    }

    public function getRoleGroup($userType) {
        switch (strtoupper($userType)) {
            case 'PLATFORM':
                return 'COMMON';
            case 'PURCHASER':
            case 'ORG':
                return 'PURCHASER';
            case 'SUPPLIER':
                return 'SUPPLIER';
        }
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function deleteData(Request $request) {
        $userId = $request->post('user_id');
        return RoleUsers::where('user_id', $userId)->update(['deleted_flag' => 'Y']);
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setRoles(array &$list, string $field = 'user_id', $fieldKey = 'role_names') {
        if (empty($list)) {
            return;
        }
        $userIds = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = '';
            if (isset($val[$field]) && $val[$field]) {
                $userIds[] = $val[$field];
            }
        }

        if (empty($userIds)) {
            return $list;
        }
        $table = $this->model->getTable();
        $roleTable = (new Roles)->getTable();
        $qurey = $this->model
                ->from($table . ' as ru')
                ->join($roleTable . ' as r', function($join) {
                    $join->on('ru.role_id', 'r.id');
                })
                ->where('r.deleted_flag', 'N')
                ->where('r.status', 'NORMAL')
                ->select('ru.user_id', 'r.name');
        $qurey->whereIn('ru.user_id', $userIds);
        $roleUserObjects = $qurey->get();
        if (empty($roleUserObjects)) {
            return $list;
        }
        $roleUsers = $roleUserObjects->toArray();
        $roleUserArr = [];
        foreach ($roleUsers as $roleUser) {
            $roleUserArr[$roleUser['user_id']][] = $roleUser['name'];
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($roleUserArr[$val[$field]])) {
                $val[$fieldKey] = implode(',', array_unique($roleUserArr[$val[$field]]));
            }
        }
    }

}
