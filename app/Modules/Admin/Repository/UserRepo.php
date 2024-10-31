<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Menus,
    Permissions,
    Purchaser,
    RoleHasPermissions,
    RoleUsers,
    Roles,
    Supplier,
    User,
    UserContact,
    UserPurchaser,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\MenusRepo;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Redis
};
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Font
};

class UserRepo extends Repository {

    protected $model;
    protected $sorts = [
        'number',
        'name',
        'phone',
        'status',
        'created_at',
    ];

    public function __construct() {
        $this->model = new User();
        parent::__construct($this->model);
    }

    public function getList(Request $request) {
        $table = $this->model->getTable();
        $query = $this->model
                ->from($table . ' as u')
                ->join('user_purchaser as up', 'up.user_id', '=', 'u.user_id', 'left')
                ->join('purchaser as p', 'p.id', '=', 'up.purchaser_id', 'left')
                ->selectRaw('u.user_id,u.user_type,u.phone,u.username,u.email,u.image,'
                . 'u.realname,u.full_pinyin,u.birthday,u.gender,u.enable,'
                . 'u.status,u.is_super,u.sub,u.created_at,u.updated_at,u.deleted_flag');
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $query->groupBy('u.user_id');
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $data = $object->toArray();
        $purchaserId = $request->get('purchaser_id');
        (new UserPurchaserRepo)->setOrgs($data, 'user_id', $purchaserId);
        (new UserSupplierRepo)->setSuppliers($data, 'user_id');
        foreach ($data as &$item) {
            $item['user_type_name'] = $this->getUserTypeText($item['user_type']);
            $item['gender_name'] = $this->getGenderText($item['gender']);
            $item['enable_name'] = $this->getEnableText($item['enable']);
            $this->setCurPurChaser($item, $purchaserId);
        }
        (new RolesUserRepo)->setRoles($data);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function setCurPurChaser(&$data, $curId = null) {
        if (empty($data['user_purchaser']) && empty($data['user_supplier'])) {
            return $data['org_name'] = '';
        }
        if (!empty($data['user_purchaser']) && $data['user_type'] !== 'SUPPLIER') {
            $curId = $data['user_purchaser'][0]['purchaser_id'];
            $data['org_name'] = !empty($data['user_purchaser'][0]['long_name']) ? $data['user_purchaser'][0]['long_name'] : $data['user_purchaser'][0]['name'];

            return;
        }
        if (!empty($data['user_supplier']) && $data['user_type'] == 'SUPPLIER') {
            $data['org_name'] = $data['user_supplier']['supplier_name'];
            return;
        }
        $data['org_name'] = '';
        return;
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $sort = 'u.' . $sort;
        $query->orderBy($sort, $order);
        if ($sort !== 'created_at') {
            $query->orderBy('u.created_at', 'DESC');
        }
    }

    public function info($userId) {
        $table = $this->model->getTable();
        $query = $this->model
                ->from($table . ' as u')
                ->selectRaw('u.user_id,u.user_type,u.phone,u.username,u.email,u.image,'
                . 'u.realname,u.full_pinyin,u.birthday,u.gender,u.enable,'
                . 'u.status,u.is_super,u.sub,u.created_at,u.updated_at,u.deleted_flag');
        $query->where('u.user_id', $userId);
        $query->where('u.deleted_flag', 'N');
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $data = $object->toArray();
        $data['user_type_name'] = $this->getUserTypeText($data['user_type']);
        $data['gender_name'] = $this->getGenderText($data['gender']);
        $data['enable_name'] = $this->getEnableText($data['enable']);
        (new UserPurchaserRepo)->setOrg($data);
        $data['contact'] = UserContact::where('user_id', $userId)->get();
        (new UserSupplierRepo)->setSupplier($data);
        return $data;
    }

    /**
     * @param int $userId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($userId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $userType = strtoupper(trim($request->user_type));
        $flag = User::where('user_id', $userId)->update([
            'status' => 1,
            'user_type' => !empty($request->user_type) ? trim($request->user_type) : 'PLATFORM',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $admin->user_id,
            'phone' => !empty($request->phone) ? trim($request->phone) : null,
            'email' => !empty($request->email) ? trim($request->email) : null,
            'realname' => !empty($request->realname) ? trim($request->realname) : null,
            'username' => !empty($request->username) ? trim($request->username) : trim($request->realname),
            'full_pinyin' => !empty($request->full_pinyin) ? trim($request->full_pinyin) : null,
            'birthday' => !empty($request->birthday) ? trim($request->birthday) : null,
            'gender' => !empty($request->gender) ? trim($request->gender) : 'SECRECY',
            'image' => !empty($request->image) ? trim($request->image) : null,
        ]);
        UserPurchaser::where('user_id', $userId)->update(['deleted_flag' => 'Y']);
        UserSupplier::where('user_id', $userId)->update(['deleted_flag' => 'Y']);
        if (empty($userType) || in_array($userType, ['PURCHASER', 'PLATFORM', 'ORG'])) {
            $orgRepo = new OrgRepo();
            $purchaserIds = [];
            foreach ($request->user_purchaser as $key => $org) {
                $botPurchaserId = $orgRepo->getBottomId($org['purchaser_id']);
                $purchaserIds[] = $botPurchaserId;
                UserPurchaser::upsert([
                    'user_id' => $userId,
                    'purchaser_id' => !empty($org['purchaser_id']) ? trim($org['purchaser_id']) : 0,
                    'bot_purchaser_id' => $botPurchaserId,
                    'position' => !empty($org['position']) ? trim($org['position']) : '',
                    'is_manager' => !empty($org['is_manager']) ? trim($org['is_manager']) : 0,
                    'is_default' => !empty($org['is_default']) ? trim($org['is_default']) : 0,
                    'sort' => $key + 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N'
                        ], ['user_id', 'purchaser_id'], ['purchaser_id', 'position', 'is_manager', 'sort', 'is_default', 'deleted_flag']);
            }
            $count = UserPurchaser::where('user_id', $userId)->where('deleted_flag', 'N')->where('is_default', 1)->count();
            if ($count === 0) {
                UserPurchaser::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_default' => '1']);
            }
            $mcount = UserPurchaser::where('user_id', $userId)
                    ->where('deleted_flag', 'N')
                    ->where('is_manager', 1)
                    ->count();
            if ($mcount === 0) {
                UserPurchaser::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_manager' => 1]);
            }
            RoleUsers::where('user_id', $userId)
                    ->whereNotIn('team_id', $purchaserIds)
                    ->where('deleted_flag', 'N')
                    ->update(['deleted_flag' => 'Y']);
        } elseif ($userType === 'SUPPLIER') {
            $userSupplier = $request->user_supplier;
            UserSupplier::upsert([
                'user_id' => $userId,
                'supplier_id' => !empty($userSupplier['supplier_id']) ? trim($userSupplier['supplier_id']) : 0,
                'is_manager' => !empty($userSupplier['is_manager']) ? trim($userSupplier['is_manager']) : 0,
                'is_default' => !empty($userSupplier['is_default']) ? trim($userSupplier['is_default']) : 0,
                'position' => !empty($userSupplier['position']) ? trim($userSupplier['position']) : '',
                'sort' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'deleted_flag' => 'N'
                    ], ['user_id', 'supplier_id'], ['supplier_id', 'is_manager', 'sort', 'is_default', 'deleted_flag']);
            $count = UserSupplier::where('user_id', $userId)->where('deleted_flag', 'N')
                    ->where('is_default', 1)
                    ->count();
            if ($count === 0) {
                UserPurchaser::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_default' => '1']);
            }
            $mcount = UserSupplier::where('user_id', $userId)->where('deleted_flag', 'N')
                    ->where('is_manager', 1)
                    ->count();
            if ($mcount === 0) {
                UserSupplier::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_manager' => '1']);
            }
        }
        $contactTypes = ['ADDRESS', 'EMAIL', 'PHONE'];
        UserContact::where('user_id', $userId)->delete();
        $dataList = [];
        foreach ($request->contact as $contact) {
            if (empty($contact['contact_type']) || !in_array($contact['contact_type'], $contactTypes)) {
                continue;
            }
            $dataList[] = [
                'user_id' => $userId,
                'contact_type' => !empty($contact['contact_type']) ? trim($contact['contact_type']) : '',
                'contact_value' => !empty($contact['contact_value']) ? trim($contact['contact_value']) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        if (empty($dataList)) {
            return $flag;
        }
        UserContact::upsert($dataList, ['user_id', 'contact_type', 'contact_value'], ['user_id', 'contact_type', 'contact_value']);
        $manageMail = env('MANAGE_MAIL');
        if ($userId == '1' && !empty($request->email) && $manageMail <> $request->email) {
            $env = file_get_contents(base_path() . '/.env');
            $newenv = str_replace($manageMail, $request->email, $env);
            file_put_contents(base_path() . '/.env', $newenv);
        }
        return $flag;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $password = 'ecp@2024';
        $userType = strtoupper(trim($request->user_type));
        $userId = User::insertGetId([
                    'status' => 1,
                    'user_type' => !empty($userType) ? $userType : 'PLATFORM',
                    'created_at' => date('Y-m-d H:i:s'),
                    'username' => !empty($request->username) ? trim($request->username) : trim($request->realname),
                    'phone' => !empty($request->phone) ? trim($request->phone) : null,
                    'email' => !empty($request->email) ? trim($request->email) : null,
                    'realname' => !empty($request->realname) ? trim($request->realname) : null,
                    'full_pinyin' => !empty($request->full_pinyin) ? trim($request->full_pinyin) : null,
                    'birthday' => !empty($request->birthday) ? trim($request->birthday) : null,
                    'gender' => !empty($request->gender) ? trim($request->gender) : 'SECRECY',
                    'image' => !empty($request->image) ? trim($request->image) : null,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'password_flag' => 1,
        ]);

        if (empty($userType) || in_array($userType, ['PURCHASER', 'PLATFORM', 'ORG'])) {
            $orgRepo = new OrgRepo();
            foreach ($request->user_purchaser as $key => $org) {
                $botPurchaserId = $orgRepo->getBottomId($org['purchaser_id']);
                UserPurchaser::upsert([
                    'user_id' => $userId,
                    'purchaser_id' => !empty($org['purchaser_id']) ? trim($org['purchaser_id']) : 0,
                    'bot_purchaser_id' => $botPurchaserId,
                    'position' => !empty($org['position']) ? trim($org['position']) : '',
                    'is_manager' => !empty($org['is_manager']) ? trim($org['is_manager']) : 0,
                    'is_default' => !empty($org['is_default']) ? trim($org['is_default']) : 0,
                    'sort' => $key + 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'deleted_flag' => 'N'
                        ], ['user_id', 'purchaser_id'], ['purchaser_id', 'position', 'is_manager', 'sort', 'is_default']);
            }
            $count = UserPurchaser::where('user_id', $userId)->where('deleted_flag', 'N')->where('is_default', 1)->count();
            if ($count === 0) {
                UserPurchaser::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_default' => '1']);
            }
            $mcount = UserPurchaser::where('user_id', $userId)->where('deleted_flag', 'N')->where('is_manager', 1)->count();
            if ($mcount === 0) {
                UserPurchaser::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_manager' => 1]);
            }
        } elseif ($userType === 'SUPPLIER') {
            $userSupplier = $request->user_supplier;
            UserSupplier::upsert([
                'user_id' => $userId,
                'supplier_id' => !empty($userSupplier['supplier_id']) ? trim($userSupplier['supplier_id']) : 0,
                'is_manager' => !empty($userSupplier['is_manager']) ? trim($userSupplier['is_manager']) : 0,
                'is_default' => !empty($userSupplier['is_default']) ? trim($userSupplier['is_default']) : 0,
                'position' => !empty($userSupplier['position']) ? trim($userSupplier['position']) : '',
                'sort' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'deleted_flag' => 'N'
                    ], ['user_id', 'supplier_id'], ['supplier_id', 'is_manager', 'sort', 'is_default']);
            $count = UserSupplier::where('user_id', $userId)->where('deleted_flag', 'N')
                    ->where('is_default', 1)
                    ->count();
            if ($count === 0) {
                UserPurchaser::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_default' => '1']);
            }
            $mcount = UserSupplier::where('user_id', $userId)->where('deleted_flag', 'N')
                    ->where('is_manager', 1)
                    ->count();
            if ($mcount === 0) {
                UserSupplier::where('user_id', $userId)
                        ->where('deleted_flag', 'N')
                        ->where('sort', 1)
                        ->update(['is_manager' => '1']);
            }
        }
        $contactTypes = ['ADDRESS', 'EMAIL', 'PHONE'];
        $dataList = [];
        foreach ($request->contact as $contact) {
            if (empty($contact['contact_type']) || !in_array($contact['contact_type'], $contactTypes)) {
                continue;
            }
            $dataList[] = [
                'user_id' => $userId,
                'contact_type' => !empty($contact['contact_type']) ? trim($contact['contact_type']) : '',
                'contact_value' => !empty($contact['contact_value']) ? trim($contact['contact_value']) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        if (!empty($dataList)) {
            UserContact::upsert($dataList, ['user_id', 'contact_type', 'contact_value'], ['user_id', 'contact_type', 'contact_value']);
        }

        if (!empty($request->email)) {
            app(Dispatcher::class)->dispatch
                    (new SendMailJob([
                'email' => $request->email,
                'phone' => $request->phone,
                    ], 'USER_SEND'));
        }
        return $userId;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return User::whereIn('user_id', $ids)->update([
                    'enable' => 1,
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'disabled_at' => null,
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return User::whereIn('user_id', $ids)->update([
                    'enable' => 0,
                    'disabled_by' => $admin->user_id,
                    'disabled_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return User::whereIn('user_id', $ids)->update(['deleted_flag' => 'Y']);
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('u.deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('u.realname', 'like', '%' . $keyword . '%')
                        ->orWhere('u.phone', 'like', '%' . $keyword . '%')
                        ->orWhere('u.email', 'like', '%' . $keyword . '%')
                        ->orWhere('u.full_pinyin', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->status)) {
            $status = $request->status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('u.status', $statusies);
        } else {
            $query->where('u.status', 1);
        }
        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            foreach ($enables as &$enable) {
                $enable = $enable - 1;
            }
            $query->whereIn('u.enable', $enables);
        }
        if (!empty($request->user_type)) {
            $userTypes = $request->user_type;
            //$userTypeArr = is_array($userTypes) ? $userTypes : explode(',', trim($userTypes));
            if ($userTypes == 'SUPPLIER') {
                $query->where('u.user_type', $userTypes);
            } else {
                $query->where('p.purchaser_type', $userTypes);
            }
        }
        if (!empty($request->name)) {
            $this->matchs($query, 'name', $request->name[0], $request->name[1]);
        }
        if (!empty($request->phone)) {
            $this->matchs($query, 'u.phone', $request->phone[0], $request->phone[1]);
        }
        if (!empty($request->purchaser_id)) {
            $upTable = (new UserPurchaser())->getTable();
            $pTable = (new \App\Common\Models\Purchaser())->getTable();
            $groupId = $request->purchaser_id;
            $query->whereRaw('EXISTS(SELECT p.id FROM ' . $pTable
                    . ' as p INNER JOIN ' . $upTable . ' as up ON p.id =up.purchaser_id'
                    . '  WHERE (p.id= ' . $groupId . ' OR FIND_IN_SET(' . $groupId . ',p.parent_ids))'
                    . ' AND up.user_id=u.user_id AND up.deleted_flag=\'N\''
                    . ' AND p.deleted_flag=\'N\')');
        }
        if (!empty($request->purchaser_name)) {
            $upTable = (new UserPurchaser())->getTable();
            $pTable = (new \App\Common\Models\Purchaser())->getTable();
            $sTable = (new \App\Common\Models\Supplier())->getTable();
            $usTable = (new \App\Common\Models\UserSupplier())->getTable();
            $psql = 'EXISTS(SELECT p.id FROM ' . $pTable
                    . ' as p INNER JOIN ' . $upTable . ' as up ON p.id =up.purchaser_id'
                    . '  WHERE p.long_name like  \'%' . $request->purchaser_name . '%\'';
            if (!empty($request->purchaser_id)) {
                $psql .= ' AND (p.id= ' . $request->purchaser_id . ' OR FIND_IN_SET('
                        . $request->purchaser_id . ',p.parent_ids))';
            }
            $psql .= ' AND up.user_id=u.user_id AND up.deleted_flag=\'N\''
                    . ' AND p.deleted_flag=\'N\' )';
            $ssql = 'EXISTS(SELECT s.id FROM ' . $sTable
                    . ' as s INNER JOIN ' . $usTable . ' as us ON s.id =us.supplier_id'
                    . '  WHERE s.name like  \'%' . $request->purchaser_name . '%\'';
            $ssql .= ' AND us.user_id=u.user_id AND s.deleted_flag=\'N\''
                    . ' AND us.deleted_flag=\'N\')';
            $query->where(function ($q)use($psql, $ssql) {
                $q->whereRaw($psql)
                        ->orWhereRaw($ssql);
            });
        }
        if (!empty($request->role_name)) {
            $ruTable = (new \App\Common\Models\RoleUsers())->getTable();
            $rTable = (new \App\Common\Models\Roles())->getTable();
            $query->whereRaw('EXISTS(SELECT ru.id FROM ' . $ruTable
                    . ' as ru INNER JOIN ' . $rTable . ' as r ON r.id =ru.role_id'
                    . '  WHERE (r.name like  \'%' . $request->role_name . '%\')'
                    . ' AND up.user_id=u.user_id )');
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('u.created_at', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('u.created_at', $createAts);
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setUsers(array &$list, string $field = 'user_id', $fieldKey = 'user') {
        if (empty($list)) {
            return;
        }
        $userIds = [];
        foreach ($list as &$val) {
            if (empty($val[$fieldKey])) {
                $val[$fieldKey] = '';
            }

            if (isset($val[$field]) && $val[$field]) {
                $userIds[] = $val[$field];
            }
        }

        if (empty($userIds)) {
            return $list;
        }
        $qurey = $this->model->select('user_id', 'realname');
        $qurey->whereIn('user_id', $userIds);
        $qurey->where('deleted_flag', 'N');
        $userObjects = $qurey->get();
        if (empty($userObjects)) {
            return $list;
        }
        $users = $userObjects->toArray();
        $userArr = [];
        foreach ($users as $user) {
            $userArr[$user['user_id']] = $user['realname'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($userArr[$val[$field]])) {
                $val[$fieldKey] = $userArr[$val[$field]];
            }
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc 获取行业
     */
    public function setUser(array &$arr, string $field = 'user_id', $fieldKey = 'user') {
        if (empty($arr)) {
            return;
        }
        $userId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $userId = $arr[$field];
        }

        $arr[$fieldKey] = '';
        if (empty($userId)) {
            return $arr;
        }
        $arr[$fieldKey] = $this->model
                ->where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('realname');
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc 获取行业
     */
    public function setStrUsers(array &$arr, string $field = 'user_id', $fieldKey = 'user') {
        if (empty($arr)) {
            return;
        }
        $userId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $userId = $arr[$field];
        }
        $userIds = explode(',', $userId);
        $arr[$fieldKey] = '';
        if (empty($userIds)) {
            return $arr;
        }
        $users = $this->model
                ->whereIn('user_id', $userIds)
                ->where('deleted_flag', 'N')
                ->get()
                ->toArray();
        $user_realnames = array_column($users, 'realname');
        $arr[$fieldKey] = implode(',', $user_realnames);
    }

    /**
     * 登录管理员信息获取
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \App\Exceptions\Admin\AuthTokenException
     */
    public function orgs(Request $request) {
        if (empty($request->user_id)) {
            check(false, '请选择用户');
        }
        $orgIds = UserPurchaser::where('user_id', $request->user_id)
                ->where('deleted_flag', 'N')
                ->pluck('bot_purchaser_id');
        if (empty($orgIds)) {
            return [];
        }
        $obejct = Purchaser::whereIn('id', $orgIds)
                ->where('deleted_flag', 'N')
                ->where('enable', '1')
                ->whereIn('purchaser_type', ['PURCHASER', 'PLATFORM'])
                ->selectRaw('*')
                ->get();
        return empty($obejct) ? [] : $obejct->toArray();
    }

    public function menusTree(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            $data['ret'] = false;
            $data['message'] = '管理员数据无法查询';
            return $data;
        }
        $data['user_type'] = $admin['user_type'];
        $data['user_id'] = $admin['user_id'];
        $user_type = $admin['user_type'];
        $menus_data = [];
        if (!empty($request->type)) {
            $menus_data['type'] = $request->type;
        }
        if (!empty($request->path)) {
            $menus_data['path'] = $request->path;
        }
        $menus = new MenusRepo;
        return $menus->getMenusByUser($user_type, $menus_data);
    }

    public function roleOrgs(Request $request) {
        if (empty($request->user_id)) {
            check(false, '请选择用户');
        }
        $userId = $request->user_id;
        $userType = User::where('user_id', $userId)->where('deleted_flag', 'N')->value('user_type');
        if ($userType === 'SUPPLIER') {
            return $this->roleSupplier($request);
        }
        $user = $this->info($userId);
        $roleUsers = (new RoleUsers)->getTable();
        $orgList = $this->orgs($request);
        $ret = [];
        foreach ($orgList as &$item) {
            $item['children'] = [];
            $ret[$item['id']]['org_name'] = $item['name'];
            $ret[$item['id']]['team_id'] = (string) $item['id'];
            $ret[$item['id']]['purchaser_id'] = (string) $item['id'];
            $ret[$item['id']]['userType'] = $item['purchaser_type'];
            $ret[$item['id']]['children'] = [];
        }
        $rolesTable = (new Roles)->getTable();
        $userPurchaserTable = (new UserPurchaser)->getTable();
        $query = Roles::from($rolesTable . ' as r')
                ->selectRaw('ru.content_id,r.name as role_name,r.role_group,r.remarks,'
                        . 'ru.role_group as role_user_group,ru.team_id,p.name as org_name,ru.role_id')
                ->join($roleUsers . ' as ru', 'ru.role_id', '=', 'r.id')
                ->join($userPurchaserTable . ' as up', function($join) {
                    $join->on('up.bot_purchaser_id', '=', 'ru.team_id')
                    ->on('up.user_id', 'ru.user_id');
                })
                ->join('purchaser as p', function($join) {
                    $join->on('p.id', '=', 'up.bot_purchaser_id');
                })
                ->where('up.deleted_flag', 'N')
                ->where('p.enable', 1)
                ->where('p.deleted_flag', 'N')
                ->where('r.deleted_flag', 'N')
                ->where('r.status', 'NORMAL')
                ->where('ru.user_id', $userId);
        $query->whereIn('r.role_group', ['PURCHASER', 'COMMON', 'SYSTEM', 'PLATFORM'])
                ->whereIn('ru.role_group', ['PURCHASER', 'COMMON', 'SYSTEM', 'PLATFORM']);
        $roles = $query
                ->groupBy('ru.role_id')
                ->groupBy('ru.team_id')
                ->get()
                ->toArray();
        if (empty($roles)) {
            $user['roles'] = array_values($ret);
            return $user;
        }
        foreach ($roles as $role) {
            $role['team_id'] = (string) $role['team_id'];
            $role['role_id'] = (string) $role['role_id'];
            $role['content_id'] = (string) $role['content_id'];
            $ret[$role['team_id']]['org_name'] = $role['org_name'];
            $ret[$role['team_id']]['team_id'] = (string) $role['team_id'];
            $ret[$role['team_id']]['purchaser_id'] = (string) $role['team_id'];
            $ret[$role['team_id']]['children'][] = $role;
        }
        $user['roles'] = array_values($ret);
        return $user;
    }

    public function roleSupplier(Request $request) {
        if (empty($request->user_id)) {
            check(false, '请选择用户');
        }
        $userId = $request->user_id;
        $user = $this->info($userId);
        $roleUsers = (new RoleUsers)->getTable();
        $orgList = !empty($user['user_supplier']) ? [$user['user_supplier']] : [];
        $ret = [];
        foreach ($orgList as &$item) {
            $item['children'] = [];
            $ret[$item['supplier_id']]['org_name'] = $item['supplier_name'];
            $ret[$item['supplier_id']]['team_id'] = (string) $item['supplier_id'];
            $ret[$item['supplier_id']]['userType'] = $user['user_type'];
            $ret[$item['supplier_id']]['children'] = [];
        }
        $rolesTable = (new Roles)->getTable();
        $supplierTable = (new Supplier)->getTable();
        $query = Roles::from($rolesTable . ' as r')
                ->selectRaw('ru.content_id,r.name as role_name,r.role_group,r.remarks,'
                        . 'ru.role_group as role_user_group,ru.team_id,s.name as org_name,ru.role_id')
                ->join($roleUsers . ' as ru', function($join) {
                    $join->on('ru.role_id', '=', 'r.id');
                })
                ->leftJoin($supplierTable . ' as s', function($join) {
                    $join->on('s.id', '=', 'ru.team_id')
                    ->where('s.deleted_flag', 'N');
                })
                ->where('r.deleted_flag', 'N')
                ->where('r.status', 'NORMAL')
                ->where(function($q)use($userId) {
            $q->where('ru.user_id', $userId);
        });
        $query->whereIn('r.role_group', ['SUPPLIER', 'COMMON'])
                ->whereIn('ru.role_group', ['SUPPLIER', 'COMMON']);
        $roles = $query->get()
                ->toArray();
        if (empty($roles)) {
            $user['roles'] = array_values($ret);
            return $user;
        }
        foreach ($roles as $role) {
            $role['team_id'] = (string) $role['team_id'];
            $role['role_id'] = (string) $role['role_id'];
            $role['content_id'] = (string) $role['content_id'];

            $ret[$role['team_id']]['org_name'] = $role['org_name'];
            $ret[$role['team_id']]['team_id'] = (string) $role['team_id'];
            $ret[$role['team_id']]['children'][] = $role;
        }


        $user['roles'] = array_values($ret);
        return $user;
    }

    /**
     * 登录管理员信息获取
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \App\Exceptions\Admin\AuthTokenException
     */
    public function roles(Request $request) {
        if (empty($request->user_id)) {
            check(false, '请选择用户');
        }
        $userId = $request->user_id;
        $userType = User::where('user_id', $userId)->where('deleted_flag', 'N')->value('user_type');
        $roleUsers = (new RoleUsers)->getTable();
        $query = Menus::from('menus as m')
                ->selectRaw('ru.content_id,r.name as role_name,p.route,p.method,'
                        . 'p.shortcut_flag,p.name,m.sort,m.type,r.role_group,'
                        . 'ru.role_group as role_user_group,m.menu_type,p.permission_type,'
                        . 'm.path,m.perm_id,p.type as perm_type,p.is_share,rm.scope')
                ->join('role_has_menus as rm', 'm.id', '=', 'rm.menu_id', 'left')
                ->join('roles as r', 'rm.role_id', '=', 'r.id')
                ->join('permissions as p', 'p.id', '=', 'm.perm_id')
                ->join($roleUsers . ' as ru', 'ru.role_id', '=', 'r.id')
                ->where('r.deleted_flag', 'N')
                ->where('p.status', 'NORMAL')
                ->where('m.status', 'NORMAL')
                ->where('r.status', 'NORMAL')
                ->where('p.deleted_flag', 'N')
                ->where(function($q)use($userId) {
            $q->where('ru.user_id', $userId)
            ->orWhere('p.is_share', 'N');
        });
        if ($userType === 'SUPPLIER') {
            $query->whereIn('r.role_group', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                    ->whereIn('ru.role_group', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                    ->whereIn('m.menu_type', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                    ->whereIn('p.permission_type', ['SUPPLIER', 'SYSTEM', 'COMMON']);
        } else {
            $query->whereIn('r.role_group', ['PURCHASER', 'COMMON', 'SYSTEM'])
                    ->whereIn('ru.role_group', ['PURCHASER', 'COMMON', 'SYSTEM'])
                    ->whereIn('m.menu_type', ['PURCHASER', 'COMMON', 'SYSTEM'])
                    ->whereIn('p.permission_type', ['PURCHASER', 'COMMON', 'SYSTEM']);
        }
        $menus = $query
                ->groupBy('ru.role_id')
                ->groupBy('ru.team_id')
                ->get()
                ->toArray();
        if (empty($menus)) {
            return [];
        }
        (new OrgRepo())->setOrgs($menus, 'content_id');
        return $menus;
    }

    public function getUserTypeText($userType) {
        switch (strtoupper($userType)) {
            case 'SUPPLIER':
                return '供应商';
            case 'ORG':
                return '组织';
            case 'PLATFORM':
                return '平台';
        }
    }

    public function getGenderText($gender) {
        switch (strtoupper($gender)) {
            case 'FEMALE':
                return '女';
            case 'MALE':
                return '男';
            default:
                return '保密';
        }
    }

    public function getEnableText($gender) {
        switch (strtoupper($gender)) {
            case '1':
                return '可用';
            default:
                return '禁用';
        }
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $table = $this->model->getTable();
        $query = $this->model
                ->from($table . ' as u')
                ->selectRaw('u.user_id,u.user_type,u.phone,u.username,u.email,u.image,'
                . 'u.realname,u.full_pinyin,u.birthday,u.gender,u.enable,'
                . 'u.status,u.is_super,u.sub,u.created_at,u.updated_at,u.deleted_flag');
        if ($request->type === 'ALL') {
            $query->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('deleted_flag', 'N')
                    ->whereIn('u.user_id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $data = $object->toArray();
        $purchaserId = $request->get('purchaser_id');
        (new UserPurchaserRepo)->setOrgs($data, 'user_id', $purchaserId);
        (new UserSupplierRepo)->setSuppliers($data, 'user_id');
        foreach ($data as &$item) {
            $item['user_type_name'] = $this->getUserTypeText($item['user_type']);
            $item['gender_name'] = $this->getGenderText($item['gender']);
            $item['enable_name'] = $this->getEnableText($item['enable']);
        }
        (new RolesUserRepo)->setRoles($data);
        $headName = $this->getHeadName();
        $xlsName = "User_" . date("YmdHis", time()) . uniqid(); //文件名称
        return $this->downloadExcel($xlsName, $data, $headName);
    }

    private $styleArray = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
        'font' => [
            'name' => 'Arial',
            'bold' => false,
            'italic' => false,
            'size' => 9,
            'underline' => Font::UNDERLINE_NONE,
            'strikethrough' => false,
            'color' => [
                'rgb' => '000000'
            ]
        ],
        'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '00000000'],
            ],
        ],
    ];

    public function setExcelRow($sheet, $col, $row, $value, $width) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->applyFromArray($this->styleArray);
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    /**
     * 导出
     * @param type $request Description
     * @param $name
     * @param array $data
     * @param array $head
     * @return array
     */
    public function downloadExcel($name, $data = [], $head = []) {
        $count = count($head);  //计算表头数量
        $spreadsheet = Excel::newSpreadsheet();
        $fillstyle = $styleArray = $this->styleArray;
        $fillstyle['fill'] = [
            'fillType' => 'linear',
            'rotation' => 0.0,
            'startColor' => [
                'rgb' => 'EEEEEE'
            ],
            'endColor' => [
                'argb' => 'FFEEEEEE'
            ]
        ];
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $clumnName = strtoupper(chr($i));
            $this->setExcelRow($sheet, $clumnName, 1, $head[$i - 65], 20);
            $sheet->getStyle($clumnName . 1)->applyFromArray($fillstyle);
        }
        $row = 2;
        foreach ($data as $key => $item) {

            //数字转字母从65开始：
            $groupName = '';
            if ($item['user_type'] === 'SUPPLIER' && !empty($item['user_supplier'])) {
                $groupName = $item['user_supplier']['supplier_name'];
            } elseif (!empty($item['user_purchaser']) && $item['user_type'] !== 'SUPPLIER') {
                $groupName = implode("\r\n", array_unique(array_column($item['user_purchaser'], 'name')));
            }
            $this->setExcelRow($sheet, 'A', $row, ' ' . ($key + 1), 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, ' ' . $item['realname'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, ' ' . $item['email'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, ' ' . $item['gender_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $groupName, 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['created_at'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $realtive = "/download/" . date("Ymd") . '/';
        $filename = $name . '.xlsx';
        $filedir = base_path() . '/public' . $realtive;
        @mkdir($filedir, 0777, true);
        $filepath = $filedir . $filename;
        $spreadsheet->save($filepath);
        $url = env('APP_URL') . $realtive . $filename;
        return ['file_url' => $url, 'attach_name' => $filename];
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '序号',
            '姓名',
            '邮箱',
            '性别',
            '部门',
            '创建日期',
        ];
    }

    public function orgTree(Request $request, $filed = 'id,number,name,long_name,parent_id,enable,purchaser_type') {
        $query = \App\Common\Models\Purchaser::selectRaw($filed);
        $query->where('deleted_flag', 'N');
        $query->where('enable', 1);
        $parentId = 1;
        $query->where(function($q)use($parentId) {
            $q->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
        });
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('number', 'like', '%' . $keyword . '%');
            });
        }
        $query->orderBy('created_at', 'DESC');
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        return (new OrgRepo)->handelTree($data, 0);
    }

    public function orgList(Request $request, $filed = 'id,number,name,long_name,parent_id,enable,purchaser_type') {
        $query = \App\Common\Models\Purchaser::selectRaw($filed);
        $query->where('deleted_flag', 'N');
        $query->where('enable', 1);
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('number', 'like', '%' . $keyword . '%');
            });
        }
        $clone = $query->clone();
        $query->orderBy('created_at', 'DESC');
        $total = $clone->count();
        $this->getPage($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * 修改密码
     * @return
     */
    public function change(Request $request) {
        $params = $request->post();
        $password = $params['password'];
        $user_id = $params['user_id'];
        User::where('user_id', $user_id)
                ->where('deleted_flag', 'N')
                ->update(['password' => password_hash($password, PASSWORD_DEFAULT),
                    'password_flag' => 0]);
        return [];
    }

    public function persons(Request $request) {
        $route = $request->get('route', '/admin/inquiry/add');
        $roleHasPermissions = (new RoleHasPermissions())->getTable();
        $permissions = (new Permissions())->getTable();
        $userPurchaser = (new UserPurchaser())->getTable();
        $roleUsers = (new RoleUsers())->getTable();
        $roles = (new Roles)->getTable();
        $purchaserId = $this->getPPurchaserId();
        $query = RoleUsers::from($roleUsers . ' as ru')
                ->join($roleHasPermissions . ' as rp', function($join) {
                    $join->on('ru.role_id', '=', 'rp.role_id')
                    ->where('rp.deleted_flag', 'N');
                })
                ->join($roles . ' as r', function($join) {
                    $join->on('r.id', '=', 'ru.role_id')
                    ->where('r.status', 'NORMAL')
                    ->whereIn('r.role_group', ['PURCHASER', 'SYSTEM', 'COMMON'])
                    ->where('r.deleted_flag', 'N');
                })
                ->join($permissions . ' as p', function($join) {
                    $join->on('p.id', '=', 'rp.perm_id')
                    ->whereIn('p.permission_type', ['PURCHASER', 'SYSTEM', 'COMMON'])
                    ->where('p.deleted_flag', 'N');
                })
                ->whereIn('ru.role_group', ['PURCHASER', 'SYSTEM', 'COMMON'])
                ->where('ru.deleted_flag', 'N')
                ->where(function($q)use($route) {
            $q->where('p.route', $route)
            ->orWhere('p.route', ltrim($route, '/'));
        });
        if (!empty($purchaserId)) {
            $userPurchaser = (new UserPurchaser())->getTable();
            $pTable = (new Purchaser())->getTable();
            $query->whereRaw('EXISTS(SELECT p.id FROM ' . $pTable
                    . ' as p INNER JOIN ' . $userPurchaser . ' as up ON p.id =up.purchaser_id'
                    . '  WHERE (p.id= ' . $purchaserId . ' OR FIND_IN_SET(' . $purchaserId . ',p.parent_ids))'
                    . ' AND up.user_id=ru.user_id AND up.deleted_flag=\'N\''
                    . ' AND p.deleted_flag=\'N\')');
        }
        $userIds = $query->pluck('ru.user_id');
        if (empty($userIds)) {
            return [];
        }
        $object = User::select('u.user_id', 'u.realname', 'u.email', 'u.phone', 'u.user_type')
                ->from($this->model->getTable() . ' as u')
                ->join($userPurchaser . ' as up', function($join)use($purchaserId) {
                    $join->on('up.user_id', '=', 'u.user_id')
                    ->where('up.deleted_flag', 'N');
                    if (!empty($purchaserId)) {
                        $join->where('bot_purchaser_id', $purchaserId);
                    }
                })
                ->where('u.deleted_flag', 'N')
                ->whereIn('u.user_type', ['PLATFORM', 'PURCHASER', 'ORG'])
                ->where('u.enable', 1)
                ->where('u.status', 1)
                ->whereIn('u.user_id', $userIds)
                ->groupBy('u.user_id')
                ->get();
        if (empty($object)) {
            return [];
        }
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $data = $object->toArray();
        (new UserPurchaserRepo)->setOrgs($data, 'user_id', $purchaserId);
        foreach ($data as &$item) {
            $item['user_type_name'] = $this->getUserTypeText($item['user_type']);
            unset($item['user_purchaser']);
        }
        return $data;
    }

    public function getUserByRole(Request $request) {
        $role_no = $request->role_no;
        if (empty($role_no)) {
            return [];
        }
        $roleHasPermissions = (new RoleHasPermissions())->getTable();
        $permissions = (new Permissions())->getTable();
        $userPurchaser = (new UserPurchaser())->getTable();
        $roleUsers = (new RoleUsers())->getTable();
        $roles = (new Roles)->getTable();
        $purchaserId = $this->getPPurchaserId();
        $query = RoleUsers::from($roleUsers . ' as ru')
                ->join($roleHasPermissions . ' as rp', function($join) {
                    $join->on('ru.role_id', '=', 'rp.role_id')
                    ->where('rp.deleted_flag', 'N');
                })
                ->join($roles . ' as r', function($join) {
                    $join->on('r.id', '=', 'ru.role_id')
                    ->where('r.status', 'NORMAL')
                    ->whereIn('r.role_group', ['PURCHASER', 'SYSTEM', 'COMMON', 'PLATFORM'])
                    ->where('r.deleted_flag', 'N');
                })
                ->join($permissions . ' as p', function($join) {
                    $join->on('p.id', '=', 'rp.perm_id')
                    ->whereIn('p.permission_type', ['PURCHASER', 'SYSTEM', 'COMMON', 'PLATFORM'])
                    ->where('p.deleted_flag', 'N');
                })
                ->whereIn('ru.role_group', ['PURCHASER', 'SYSTEM', 'COMMON', 'PLATFORM'])
                ->where('ru.deleted_flag', 'N')
                ->where('r.role_no', $role_no);
        if (!empty($purchaserId)) {
            $userPurchaser = (new UserPurchaser())->getTable();
            $pTable = (new Purchaser())->getTable();
            $query->whereRaw('EXISTS(SELECT p.id FROM ' . $pTable
                    . ' as p INNER JOIN ' . $userPurchaser . ' as up ON p.id =up.purchaser_id'
                    . '  WHERE (p.id= ' . $purchaserId . ' OR FIND_IN_SET(' . $purchaserId . ',p.parent_ids))'
                    . ' AND up.user_id=ru.user_id AND up.deleted_flag=\'N\''
                    . ' AND p.deleted_flag=\'N\')');
        }
        $userIds = $query->pluck('ru.user_id');
        if (empty($userIds)) {
            return [];
        }
        $object = User::select('u.user_id', 'u.realname', 'u.email', 'u.phone', 'u.user_type')
                ->from($this->model->getTable() . ' as u')
                ->join($userPurchaser . ' as up', function($join)use($purchaserId) {
                    $join->on('up.user_id', '=', 'u.user_id')
                    ->where('up.deleted_flag', 'N');
                    if (!empty($purchaserId)) {
                        $join->where('bot_purchaser_id', $purchaserId);
                    }
                })
                ->where('u.deleted_flag', 'N')
                ->whereIn('u.user_type', ['PLATFORM', 'PURCHASER', 'ORG'])
                ->where('u.enable', 1)
                ->where('u.status', 1)
                ->whereIn('u.user_id', $userIds)
                ->groupBy('u.user_id')
                ->get();
        if (empty($object)) {
            return [];
        }
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $data = $object->toArray();
        (new UserPurchaserRepo)->setOrgs($data, 'user_id', $purchaserId);
        foreach ($data as &$item) {
            $item['user_type_name'] = $this->getUserTypeText($item['user_type']);
            unset($item['user_purchaser']);
        }
        return $data;
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setEmails(array &$list, string $field = 'user_id') {
        if (empty($list)) {
            return;
        }
        $userIds = [];
        foreach ($list as &$val) {
            $val['email'] = '';
            $userIds[] = $val[$field];
        }
        if (empty($userIds)) {
            return $list;
        }
        $qurey = $this->model
                ->where('deleted_flag', 'N')
                ->where('enable', 1)
                ->selectRaw('user_id,email');
        $qurey->whereIn('user_id', $userIds);
        $userObjects = $qurey
                ->get();
        if (empty($userObjects)) {
            return $list;
        }
        $users = $userObjects->toArray();
        $userArr = [];
        foreach ($users as $user) {
            $userArr[$user['user_id']] = $user['email'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($userArr[$val[$field]])) {
                $val['email'] = $userArr[$val[$field]];
            }
        }
    }

}
