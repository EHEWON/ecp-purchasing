<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    RoleUsers,
    Roles,
    Supplier,
    User,
    UserContact,
    UserPurchaser,
    UserSupplier
};
use App\Modules\Admin\Mail\AccountCreated;
use App\Modules\Admin\Repository\{
    MenusRepo,
    RolesUserRepo,
    UserSupplierRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Mail,
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
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $table = $this->model->getTable();
        $userSupplierTable = (new UserSupplier)->getTable();
        $query = $this->model
                ->from($table . ' as u')
                ->join($userSupplierTable . ' as us', function($join)use($supplierId) {
                    $join->on('u.user_id', '=', 'us.user_id')
                    ->where('us.deleted_flag', 'N')
                    ->where('us.supplier_id', $supplierId);
                })
                ->selectRaw('u.user_id,u.user_type,u.phone,u.username,u.email,u.image,'
                . 'u.realname,u.full_pinyin,u.birthday,u.gender,u.enable,'
                . 'u.status,u.is_super,u.sub,u.created_at,u.updated_at,u.deleted_flag');
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
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
        foreach ($data as &$item) {
            $item['user_type_name'] = $this->getUserTypeText($item['user_type']);
            $item['gender_name'] = $this->getGenderText($item['gender']);
            $item['enable_name'] = $this->getEnableText($item['enable']);
        }
        (new RolesUserRepo)->setRoles($data);
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
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
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '该用户没有关联供应商');
        }
        $table = $this->model->getTable();
        $query = $this->model
                ->from($table . ' as u')
                ->selectRaw('u.user_id,u.phone,u.username,u.email,u.image,'
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
        $data['gender_name'] = $this->getGenderText($data['gender']);
        $data['enable_name'] = $this->getEnableText($data['enable']);
        $data['contact'] = UserContact::where('user_id', $userId)->get();
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
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '该用户没有关联供应商');
        }
        $flag = User::where('user_id', $userId)->update([
            'status' => 1,
            'user_type' => 'SUPPLIER',
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

        UserSupplier::where('user_id', $userId)->update(['deleted_flag' => 'Y']);
        $userSupplier = $request->user_supplier;
        UserSupplier::upsert([
            'user_id' => $userId,
            'supplier_id' => $supplierId,
            'is_manager' => !empty($userSupplier['is_manager']) ? trim($userSupplier['is_manager']) : 0,
            'is_default' => !empty($userSupplier['is_default']) ? trim($userSupplier['is_default']) : 0,
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

        $contactTypes = ['ADDRESS', 'EMAIL', 'PHONE'];
        UserContact::where('user_id', $userId)->delete();
        foreach ($request->contact as $contact) {
            if (empty($contact['contact_type']) || !in_array($contact['contact_type'], $contactTypes)) {
                continue;
            }
            UserContact::insertGetId([
                'user_id' => $userId,
                'contact_type' => !empty($contact['contact_type']) ? trim($contact['contact_type']) : '',
                'contact_value' => !empty($contact['contact_value']) ? trim($contact['contact_value']) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
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
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $curUserId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $curUserId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '该用户没有关联供应商');
        }
        $userId = User::insertGetId([
                    'status' => 1,
                    'user_type' => 'SUPPLIER',
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

        UserSupplier::upsert([
            'user_id' => $userId,
            'supplier_id' => $supplierId,
            'sort' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'deleted_flag' => 'N'
                ], ['user_id', 'supplier_id'], ['supplier_id', 'sort']);
        $count = UserSupplier::where('supplier_id', $supplierId)
                ->where('deleted_flag', 'N')
                ->where('is_default', 1)
                ->count();
        if ($count === 0) {
            UserSupplier::where('user_id', $userId)
                    ->where('supplier_id', $supplierId)
                    ->where('deleted_flag', 'N')
                    ->where('sort', 1)
                    ->update(['is_default' => '1']);
        }
        $mcount = UserSupplier::where('supplier_id', $supplierId)
                ->where('deleted_flag', 'N')
                ->where('is_manager', 1)
                ->count();
        if ($mcount === 0) {
            UserSupplier::where('user_id', $userId)
                    ->where('supplier_id', $supplierId)
                    ->where('deleted_flag', 'N')
                    ->where('sort', 1)
                    ->update(['is_manager' => '1']);
        }

        $contactTypes = ['ADDRESS', 'EMAIL', 'PHONE'];
        foreach ($request->contact as $contact) {
            if (empty($contact['contact_type']) || !in_array($contact['contact_type'], $contactTypes)) {
                continue;
            }
            UserContact::insertGetId([
                'user_id' => $userId,
                'contact_type' => !empty($contact['contact_type']) ? trim($contact['contact_type']) : '',
                'contact_value' => !empty($contact['contact_value']) ? trim($contact['contact_value']) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if (!empty($request->email)) {
            Mail::to($request->email)
                    ->send(new AccountCreated(['email' => trim($request->email),
                        'phone' => trim($request->phone)]));
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
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $ids = $request->ids;
        return User::whereIn('user_id', $ids)->update([
                    'enable' => 1,
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'disabled_at' => null,
        ]);
    }

    public function getUserTypeText($userType) {
        switch (strtoupper($userType)) {
            case 'SUPPLIER':
                return '供应商';
            case 'PURCHASER':
                return '采购商';
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
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $table = $this->model->getTable();
        $userSupplierTable = (new UserSupplier)->getTable();
        $query = $this->model
                ->from($table . ' as u')
                ->join($userSupplierTable . ' as us', function($join)use($supplierId) {
                    $join->on('u.user_id', '=', 'us.user_id')
                    ->where('us.deleted_flag', 'N')
                    ->where('us.supplier_id', $supplierId);
                })
                ->selectRaw('u.user_id,u.user_type,u.phone,u.username,u.email,u.image,'
                . 'u.realname,u.full_pinyin,u.birthday,u.gender,u.enable,'
                . 'u.status,u.is_super,u.sub,u.created_at,u.updated_at,u.deleted_flag');
        if ($request->type === 'ALL') {
            $query->where('u.deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('u.deleted_flag', 'N')
                    ->whereIn('u.user_id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }

        $data = $object->toArray();
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
            $this->setExcelRow($sheet, 'A', $row, ' ' . ($key + 1), 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, ' ' . $item['realname'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, ' ' . $item['phone'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, ' ' . $item['gender_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['user_type'] === 'SUPPLIER' &&
                    !empty($item['user_supplier']) ? $item['user_supplier']['supplier_name'] : ''
                    , 24);
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
            '手机',
            '性别',
            '公司',
            '创建日期',
        ];
    }

    /**
     * 修改密码
     * @return
     */
    public function change(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $params = $request->post();
        $password = $params['password'];
        $user_id = $params['user_id'];
        User::where('user_id', $user_id)
                ->where('deleted_flag', 'N')
                ->update(['password' => password_hash($password, PASSWORD_DEFAULT),
                    'password_flag' => 0]);
        return [];
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
        if (!empty($request->name)) {
            $this->matchs($query, 'name', $request->name[0], $request->name[1]);
        }
        if (!empty($request->phone)) {
            $this->matchs($query, 'u.phone', $request->phone[0], $request->phone[1]);
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

    public function roleOrgs(Request $request) {
        if (empty($request->user_id)) {
            check(false, '请选择用户');
        }
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
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

    public function menusTree(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }

        $data['user_type'] = $admin['user_type'];
        $data['cur_pid'] = $supplierId;
        $data['user_id'] = $admin['user_id'];
        $menus_data = [];
        if (!empty($request->type)) {
            $menus_data['type'] = $request->type;
        }
        if (!empty($request->path)) {
            $menus_data['path'] = $request->path;
        }
        $menus = new MenusRepo;
        return $menus->getMenusByUser($admin['user_type'], $menus_data);
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function rolesuser(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $dataList = [];
        $list = $request->all();
        $userId = $request->post('user_id');
        RoleUsers::where('user_id', $userId)->delete();
        foreach ($list['role_ids'] as $roleId) {
            $dataList[] = [
                'role_group' => 'SUPPLIER',
                'role_id' => intval($roleId),
                'content_id' => $supplierId,
                'team_id' => $supplierId,
                'user_id' => intval($userId),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'created_by' => $admin->user_id,
                'updated_by' => $admin->user_id,
                'deleted_flag' => 'N',
            ];
        }
        return RoleUsers::upsert($dataList, ['role_id', 'team_id', 'user_id'], ['role_id', 'team_id', 'content_id', 'user_id', 'deleted_flag', 'updated_at', 'updated_by', 'role_group']);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
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
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $supplierId = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $ids = $request->ids;
        return User::whereIn('user_id', $ids)
                        ->update(['deleted_flag' => 'Y']);
    }

}
