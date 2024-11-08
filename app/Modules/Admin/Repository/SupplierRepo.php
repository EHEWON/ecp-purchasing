<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Message,
    MessageReceiver,
    SendLog,
    Supplier,
    SupplierAudit,
    User,
    UserContact,
    UserSupplier
};
use App\Modules\Admin\Mail\VerifyMail;
use App\Modules\Admin\Repository\{
    SupplierAttachRepo,
    SupplierAuditRepo,
    SupplierBankRepo,
    SupplierContactRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Mail,
    Redis
};
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class SupplierRepo extends Repository {

    protected $model;
    protected $sorts = [
        'number',
        'name',
        'enable',
        'disabled_at',
        'created_at',
    ];

    public function __construct() {
        $this->model = new Supplier();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'created_at') {
            $query->orderBy('created_at', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = '*') {
        $query = $this->model
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new SupplierGroupRepo())->setGroups($data);
        foreach ($data as &$item) {
            $item['source_name'] = $this->getSourceText($item['source']);
            $item['status_name'] = $this->getStatusText($item['status']);
            $item['enable_name'] = $this->getEnableText($item['enable']);
            $item['enterprise_type_name'] = $this->getEnterpriseTypeText($item['enterprise_type']);
        }
        (new SupplierAuditRepo)->setAudits($data, 'id');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id, Request $request) {
        $query = $this->model->selectRaw('*');
        $query->where('id', $id);
        $query->where('deleted_flag', 'N');
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new SupplierGroupRepo())->setGroup($data);
        (new SupplierAuditRepo)->setAudit($data, 'id');
        $data['source_name'] = $this->getSourceText($data['source']);
        $data['status_name'] = $this->getStatusText($data['status']);
        $data['enable_name'] = $this->getEnableText($data['enable']);
        $data['reg_capital'] = !empty(floatval($data['reg_capital'])) ? $data['reg_capital'] : null;
        (new PurchaserRepo)->setPurchaser($data, 'purchaser_id');
        $data['enterprise_type_name'] = $this->getEnterpriseTypeText($data['enterprise_type']);

        $userTable = (new User)->getTable();
        $userSupplierTable = (new UserSupplier)->getTable();
        $data['contact'] = (new SupplierContactRepo)->getList($id);
        $data['bank'] = (new SupplierBankRepo)->getList($id);
        $data['attach'] = (new SupplierAttachRepo)->getQualifications($id);
        $purchaserId = !empty($request->purchaser_id) ? $request->purchaser_id : $data['purchaser_id'];
        $userObj = User::select('u.realname', 'u.email', 'u.phone')
                ->from($userTable . ' as u')
                ->join($userSupplierTable . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->where('u.user_type', 'SUPPLIER')
                ->where('u.deleted_flag', 'N')
                ->where('u.user_type', 'SUPPLIER')
                ->where('us.is_manager', 1)
                ->where('us.supplier_id', $id)
                ->first();
        if (empty($userObj)) {
            $data['user'] = [];
            return $data;
        }
        $user = $userObj->toArray();
        $user['phone_email'] = !empty($user['phone']) && !empty($user['email']) ? $user['phone'] . '/' . $user['email'] :
                (!empty($user['phone']) ? $user['phone'] : $user['email']);
        unset($user['phone'], $user['email']);
        $data['user'] = $user;
        return $data;
    }

    /**
     * @param int $supplierId
     * @param Request $request
     * 
     * @return array
     */
    public function edited($supplierId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $status = $request->status;
        $supplier = Supplier::where('deleted_flag', 'N')
                ->find($supplierId);
        if (empty($supplier)) {
            check(false, '供应商不存在');
        }
        if ($supplier->status === Supplier::STATUS_APPROVING) {
            check(false, '当前供应商处于审核中,不能编辑');
        }
        if ($supplier->status === 'APPROVED') {
            $count = SupplierAudit::where('supplier_id', $supplierId)
                    ->where('status', SupplierAudit::STATUS_PENDING)
                    ->where('audit_type', 'CHANGE')
                    ->count();
            if ($count) {
                check(false, '当前供应商存在变更审核信息,不能进行变更');
            }
            $audit = SupplierAudit::where('supplier_id', $supplierId)->where('status', SupplierAudit::STATUS_DRAFT)->first();
            $auditData = [
                'base' => json_encode([
                    'supplier_no' => $supplier->supplier_no,
                    'enable' => $supplier->enable,
                    'source' => $supplier->source,
                    'filled_at' => $supplier->filled_at,
                    'registered_at' => $supplier->registered_at,
                    'industry_classification' => $supplier->industry_classification,
                    'supplier_group_id' => !empty($request->supplier_group_id) ? intval($request->supplier_group_id) : '1',
                    'name' => trim($request->name),
                    'purchaser_id' => !empty($request->purchaser_id) ? intval($request->purchaser_id) : 1,
                    'enterprise_type' => !empty($request->enterprise_type) ? $request->enterprise_type : '',
                    'profile' => !empty($request->profile) ? $request->profile : '',
                    'reg_capital' => !empty(floatval($request->reg_capital)) ? $request->reg_capital : '',
                    'social_credit_code' => !empty($request->social_credit_code) ? $request->social_credit_code : '',
                    'legal_representative' => !empty($request->legal_representative) ? $request->legal_representative : '',
                    'scope_of_operation' => !empty($request->scope_of_operation) ? $request->scope_of_operation : '',
                    'remarks' => !empty($request->remarks) ? $request->remarks : '',
                    'address' => !empty($request->address) ? $request->address : '',
                ]),
                'supplier_id' => $supplierId,
                'audit_type' => 'CHANGE',
                'attachs' => json_encode($request->attach),
                'banks' => json_encode($request->bank),
                'contacts' => json_encode($request->contact),
                'status' => $request['status'] == 'REVIEW' ? SupplierAudit::STATUS_DRAFT : SupplierAudit::STATUS_PENDING,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if (empty($audit)) {
                $audiId = SupplierAudit::insertGetId($auditData);
            } else {
                $audiId = $audit->id;
                SupplierAudit::where('id', $audit->id)->update($auditData);
            }
            if ($auditData['status'] === SupplierAudit::STATUS_PENDING) {
                $audiId = (new SupplierAuditRepo)->updateData($supplierId, $request);
                $bossUrl = env('BOSS_URL');
                $messageId = Message::insertGetId([
                            'receiver_type' => 'PURCHASER',
                            'content_operate' => 'SUPPLIER_AUDIT',
                            'content_id' => $supplierId,
                            'content_url' => $bossUrl . '/#/supplierManage/supplierDetail?id=' . $supplierId . '&dataType=audit&audit_id=' . $audiId,
                            'sender_id' => $supplierId,
                            'message_type' => 'SYSTEM',
                            'message_title' => '企业信息认证申请',
                            'message' => '【' . $request->name . '】已提交企业信息认证。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                $user = User::where('user_id', 1)->first();
                $dataList = [
                    'message_id' => $messageId,
                    'receiver_id' => $user->user_id,
                    'supplier_id' => $supplierId,
                    'read_flag' => 'N',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                MessageReceiver::insert($dataList);
                $emails = [$user->email];
                !empty($emails) ? (new SendLogRepo())->supplierChange($emails, trim($request->name), $supplierId) : null;
            }
            return true;
        }
        $supplierData = [
            'updated_by' => $admin->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
            'enable' => !empty($request->enable) ? intval($request->enable) : 1,
            'supplier_group_id' => !empty($request->supplier_group_id) ? intval($request->supplier_group_id) : '1',
            'status' => Supplier::STATUS_REVIEW,
            'updated_by' => $admin->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
            'name' => trim($request->name),
            'enterprise_type' => !empty($request->enterprise_type) ? $request->enterprise_type : '',
            'profile' => !empty($request->profile) ? $request->profile : '',
            'reg_capital' => !empty(floatval($request->reg_capital)) ? $request->reg_capital : '',
            'social_credit_code' => !empty($request->social_credit_code) ? $request->social_credit_code : '',
            'legal_representative' => !empty($request->legal_representative) ? $request->legal_representative : '',
            'scope_of_operation' => !empty($request->scope_of_operation) ? $request->scope_of_operation : '',
            'remarks' => !empty($request->remarks) ? $request->remarks : '',
            'address' => !empty($request->address) ? $request->address : '',
        ];
        if ($status === 'APPROVING') {
            $supplierData['status'] = Supplier::STATUS_APPROVING;
            $supplierData['filled_at'] = date('Y-m-d H:i:s');
            $supplierData['supplier_no'] = !empty($supplier->supplier_no) ? $supplier->supplier_no : $this->getSupplierNo();
        }
        $flag = Supplier::where('id', $supplierId)->update($supplierData);
        (new SupplierContactRepo)->updateData($supplierId, $request);
        (new SupplierBankRepo)->updateData($supplierId, $request);
        (new SupplierAttachRepo)->updateData($supplierId, $request);
        if ($status !== 'DRAFT' && $status !== Supplier::STATUS_REVIEW) {
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_operate' => 'SUPPLIER_AUDIT',
                        'content_id' => $supplierId,
                        'content_url' => $bossUrl . '/#/supplierManage/supplierDetail?id=' . $supplierId . '&dataType=audit&audit_id=' . $audiId,
                        'sender_id' => $supplierId,
                        'message_type' => 'SYSTEM',
                        'message_title' => '企业信息认证申请',
                        'message' => '【' . $request->name . '】已提交企业信息认证。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);
            $user = User::where('user_id', 1)->first();
            $dataList = [
                'message_id' => $messageId,
                'receiver_id' => $user->user_id,
                'supplier_id' => $supplierId,
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
            MessageReceiver::insert($dataList);
            $emails = [$user->email];
            !empty($emails) ? (new SendLogRepo())->supplierSubmit($emails, trim($request->name)) : null;
        }
        return $flag;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function register(Request $request) {
        $supplierId = Supplier::insertGetId([
                    'status' => 'DRAFT',
                    'name' => trim($request->name),
                    'registered_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'number' => $this->getNumber(),
                    'source' => 'REGISTER',
                    'purchaser_id' => 1,
                    'enable' => null,
                    'supplier_group_id' => '1'
        ]);
        $phoneEmail = trim($request->phone_email);
        if (isEmail($phoneEmail)) {
            $email = $phoneEmail;
        } elseif (is_mobile($phoneEmail)) {
            $phone = $phoneEmail;
        }
        \App\Common\Models\SupplierContact::insert([
            'supplier_id' => $supplierId,
            'contact_name' => trim($request->realname),
            'phone' => !empty($phone) ? trim($phone) : '',
            'email' => !empty($email) ? trim($email) : '',
            'default_flag' => 'Y',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->insertUser($request, $supplierId);
        Redis::del('register_supplier_code_' . trim($request->phone_email));
        return $supplierId;
    }

    public function auditInfo(int $supplierId) {
        $data = $this->getLastAudit($supplierId);
        if (!empty($data)) {
            return $data;
        }
        return $this->info($supplierId);
    }

    public function getLastAudit(int $supplierId) {
        $auditObj = SupplierAudit::where('supplier_id', $supplierId)
                ->whereIn('status', ['REVIEW', 'DRAFT', 'REJECTED'])
                ->whereIn('audit_type', ['CHANGE'])
                ->orderBy('id', 'desc')
                ->first();
        if (empty($auditObj)) {
            return [];
        }
        $audit = $audit->toArray();
        $audit['audit'] = $audit['status'];
        $data = json_decode($audit['base'], true);
        $data['id'] = $supplierId;
        $data['audit_change'] = $audit['status'];
        $data['audit_change_id'] = $audit['id'];
        $data['status'] = 'APPROVED';
        $data['reg_capital'] = !empty(floatval($data['reg_capital'])) ? $data['reg_capital'] : null;
        $data['source_name'] = $this->getSourceText($data['source']);
        $data['status_name'] = $this->getStatusText($data['status']);
        $data['enable_name'] = $this->getEnableText($data['enable']);
        $data['enable_name'] = $this->getEnableText($data['enable']);
        (new PurchaserRepo)->setPurchaser($data, 'purchaser_id');
        $data['enterprise_type_name'] = $this->getEnterpriseTypeText($data['enterprise_type']);
        $data['attach'] = json_decode($audit['attachs'], true);
        $data['bank'] = json_decode($audit['banks'], true);
        $data['contact'] = json_decode($audit['contacts'], true);
        $userTable = (new User)->getTable();
        $userSupplierTable = (new UserSupplier)->getTable();
        $userObj = User::select('u.realname', 'u.email', 'u.phone')
                ->from($userTable . ' as u')
                ->join($userSupplierTable . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->where('u.user_type', 'SUPPLIER')
                ->where('u.deleted_flag', 'N')
                ->where('u.user_type', 'SUPPLIER')
                ->where('us.is_manager', 1)
                ->where('us.supplier_id', $supplierId)
                ->first();
        if (empty($userObj)) {
            $data['user'] = [];
            return $data;
        }
        $user = $userObj->toArray();
        $user['phone_email'] = !empty($user['phone']) && !empty($user['email']) ? $user['phone'] . '/' . $user['email'] :
                (!empty($user['phone']) ? $user['phone'] : $user['email']);
        unset($user['phone'], $user['email']);
        $data['user'] = $user;
        return $audit;
    }

    public function insertUser(Request $request, int $supplierId) {
        $phoneEmail = trim($request->phone_email);
        if (isEmail($phoneEmail)) {
            $email = $phoneEmail;
        } elseif (is_mobile($phoneEmail)) {
            $phone = $phoneEmail;
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        $userId = User::insertGetId([
                    'user_type' => 'SUPPLIER',
                    'phone' => !empty($phone) ? trim($phone) : '',
                    'username' => !empty($request->realname) ? trim($request->realname) : '',
                    'email' => !empty($email) ? trim($email) : '',
                    'realname' => !empty($request->realname) ? trim($request->realname) : '',
                    'full_pinyin' => $pinyin->permalink(trim($request->realname), ''),
                    'password' => password_hash(trim($request->password), PASSWORD_DEFAULT),
                    'password_flag' => 0,
                    'status' => 1,
                    'enable' => 1,
                    'gender' => 'SECRECY',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        UserSupplier::insertGetId([
            'user_id' => $userId,
            'supplier_id' => $supplierId,
            'is_manager' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        if (!empty($phone)) {
            UserContact::insertGetId([
                'user_id' => $userId,
                'contact_type' => 'PHONE',
                'contact_value' => $phone,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if (!empty($email)) {
            UserContact::insertGetId([
                'user_id' => $userId,
                'contact_type' => 'EMAIL',
                'contact_value' => $email,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return Supplier::whereIn('id', $ids)->update([
                    'enable' => 1,
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
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
        return Supplier::whereIn('id', $ids)->update([
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
        return Supplier::whereIn('id', $ids)->delete();
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->whereIn('status', ['REVIEW', 'APPROVING', 'APPROVED', 'INVALID']);
        $query->where('deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('supplier_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->status)) {
            $status = $request->status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('status', $statusies);
        }
        if (!empty($request->statusies)) {
            $query->whereIn('status', $request->statusies);
        }
        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            $query->whereIn('enable', $enables);
        } else {
            $query->where('enable', 1);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('filled_at', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('filled_at', $createAts);
        }
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $query = $this->model->selectRaw('*');
        $this->getWhere($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['source_name'] = $this->getSourceText($item['source']);
            $item['status_name'] = $this->getStatusText($item['status']);
            $item['enable_name'] = $this->getEnableText($item['enable']);
        }
        $headName = $this->getHeadName();
        $xlsName = 'Supplier_' . date("YmdHis", time()) . uniqid(); //文件名称
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
        $styleArray = $this->styleArray;
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '供应商');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $this->setExcelRow($sheet, strtoupper(chr($i)), 2, $head[$i - 65], 20);
        }
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始：
            $this->setExcelRow($sheet, 'A', $row, ' ' . $item['number'], 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['name'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['status_name'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['source_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['v'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['filled_at'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:G2')
                ->applyFromArray($styleArray);
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
            '编码',
            '名称',
            '供应商状态',
            '使用状态',
            '注册来源',
            '提交时间',
        ];
    }

    /**
     * @desc 处理业务SKU参数
     *
     * @param array $importData 规格属性
     * @return bool
     * @author zhongyg
     * @time 2019-06-14
     */
    public function importItemHandler($importData) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $admin = Auth::guard('admin')->user();
        $data = $this->dataTrim($importData);
        $list = [];
        foreach ($data as $v) {
            $item['name'] = trim($v[0]);
            $item['realname'] = trim($v[1]);
            $item['email'] = trim($v[2]);
            $item['phone'] = trim($v[3]);
            $item['created_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['updated_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素     
            $item['created_at'] = date('Y-m-d H:i:s'); //申报要素          
            $item['updated_at'] = date('Y-m-d H:i:s'); //申报要素     
            $item['source'] = 'REGISTER';
            $item['purchaser_id'] = '1';
            $item['supplier_group_id'] = '1';
            $list[] = $item;
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        foreach ($data as $v) {
            $supplierId = Supplier::insertGetId([
                        'status' => 'DRAFT',
                        'name' => trim($v['name']),
                        'purchaser_id' => 1,
                        'registered_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'number' => $this->getNumber(),
                        'source' => 'REGISTER',
                        'supplier_group_id' => '1',
                        'enable' => 1,
            ]);
            $userId = User::insertGetId([
                        'user_type' => 'SUPPLIER',
                        'phone' => trim($v['phone']),
                        'username' => trim($v['realname']),
                        'email' => trim($v['email']),
                        'realname' => trim($v['realname']),
                        'full_pinyin' => $pinyin->permalink(trim($v['realname']), ''),
                        'password' => password_hash('ecp@2024', PASSWORD_DEFAULT),
                        'password_flag' => 0,
                        'status' => 1,
                        'gender' => 'SECRECY',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);
            UserSupplier::insertGetId([
                'user_id' => $userId,
                'supplier_id' => $supplierId,
                'is_manager' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-02-02
     */
    function dataTrim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_string($data)) {
            return trim($data);
        }
        return $data;
    }

    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    public function download2local($tmpSavePath, $remoteFile, $attach_name) {
        //设置本地临时保存目录
        $localFullFileName = $tmpSavePath . mb_convert_encoding(urldecode(basename($attach_name)), 'GB2312', 'UTF-8');
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $file = fopen($remoteFile, 'rb', null, $context);
        if ($file) {
            $newf = fopen($localFullFileName, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $localFullFileName;
    }

    public function import(Request $request) {
        $remoteFile = $request->file_url;
        $attachName = $request->attach_name;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData);
    }

    public function ready2import($localFile, $pIndex = 0) {
        //获取文件类型
        $fileType = IOFactory::identify($localFile);
        //创建PHPExcel读取对象
        $objReader = IOFactory::createReader($fileType);
        //加载文件并读取
        $officeSheet = $objReader->load($localFile);
        $data = $officeSheet->getSheet($pIndex)->toArray();
        return $data;
    }

    public function phoneEmail(Request $request) {
        $phoneEmail = $request->post('phone_email');
        $code = randomnum(6);
        $expiredSeconds = config('login.email_expired');
        if ($expiredSeconds > 3600) {
            $expired = ($expiredSeconds / 3600) . '小时';
        } else {
            $expired = ($expiredSeconds / 60) . '分钟';
        }
        $user = ['phone_email' => $phoneEmail, 'code' => $code, 'expired' => $expired];
        $sendAt = date('y-m-d H:i:s');
        if (isEmail($phoneEmail)) {
            $type = 'email';
            $title = 'VerifyMail';
            $response = Mail::to($phoneEmail)->send(new VerifyMail($user));
        } else {
            check(false, '邮箱号不正确');
        }
        Redis::setex('register_supplier_code_' . $phoneEmail, $expiredSeconds, $code);
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $phoneEmail,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => $sendAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return [];
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getNumber(&$newNumber = null) {
        $prefix = 'S';
        $qurey = $this->model->selectRaw('*');
        $number = $newNumber ? $newNumber : $qurey
                        ->where('number', 'like', $prefix . '%')
                        ->orderBy('number', 'DESC')
                        ->value('number');
        if (!empty($number)) {
            $date = substr($number, 1, 8);
            $serialSetp = substr($number, 11, 5);
            $step = intval($serialSetp);
            $step ++;
            $newNumber = $this->createSerialNo($step, $prefix, $date);
            return $newNumber;
        }
        $newNumber = $this->createSerialNo(1, $prefix, '');
        return $newNumber;
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getSupplierNo() {
        $prefix = 'S';
        $qurey = $this->model->selectRaw('*');
        $supplierNo = $qurey
                ->where('supplier_no', 'like', $prefix . '%')
                ->orderBy('supplier_no', 'DESC')
                ->value('supplier_no');
        if (!empty($supplierNo)) {
            $date = substr($supplierNo, 1, 8);
            $serialSetp = substr($supplierNo, 11, 5);
            $step = intval($serialSetp);
            $step ++;
            return $this->createSerialNo($step, $prefix, $date);
        }
        return$this->createSerialNo(1, $prefix, '');
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2019-03-11
     * @return string $code
     */
    private function createSerialNo($step = 1, $prefix = '', $date = '') {
        $time = date('Ymd');
        if (empty($date) || $date < $time) {
            $step = 1;
        }
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        return$prefix . $time . $pad;
    }

    public function getStatusText($status) {
        switch (strtoupper($status)) {
            case 'DRAFT':
                return '草稿';
            case 'REVIEW':
                return '保存';
            case 'APPROVING':
                return '提交审核';
            case 'APPROVED':
                return '审核通过';
            case 'INVALID':
                return '审核驳回';
        }
    }

    public function getSourceText($source) {
        switch (strtoupper($source)) {
            case 'BOSS':
            case 'REGISTER':
                return '瑞招采平台';
            case 'PURCHASER':
                return '采购商';
        }
    }

    public function getEnterpriseTypeText($enterpriseType) {
        switch (strtoupper($enterpriseType)) {
            case 'ENTERPRISE':
                return '企业';
            case 'STATE_ORGANS':
                return '国家机关';
            case 'PUBLIC_INSTITUTIONS':
                return '事业单位';
            case 'SOCIAL_GROUPS':
                return '社会团体';
            case 'OTHER_ORGANIZATIONAL_STRUCTURES':
                return '其他组织机构';
            case 'INDIVIDUAL_BUSINESSES':
                return '个体户';
            case 'NATURAL_PERSON':
                return '自然人';
        }
    }

    public function getEnableText($gender) {
        switch (strtoupper($gender)) {
            case '1':
                return '可用';
            case '0':
                return '禁用';
            default:
                return '';
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
    public function setSupplier(array &$data, string $field = 'supplier_id', $fieldKey = 'supplier_name') {
        if (empty($data)) {
            return;
        }
        $data[$fieldKey] = '';
        $supplierId = $data[$field];
        if (empty($supplierId)) {
            return $data;
        }
        $qurey = $this->model
                ->select('id', 'name');
        $qurey->where('id', $supplierId);
        $supplierObject = $qurey->first();
        if (empty($supplierObject)) {
            return $data;
        }
        $supplier = $supplierObject->toArray();
        $data[$fieldKey] = $supplier['name'];
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setSuppliers(array &$list, string $field = 'supplier_id', $fieldKey = 'supplier_name') {
        if (empty($list)) {
            return;
        }
        $supplierIds = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = '';
            if (isset($val[$field]) && $val[$field]) {
                $supplierIds[] = $val[$field];
            }
        }

        if (empty($supplierIds)) {
            return $list;
        }
        $qurey = $this->model
                ->select('id', 'name');
        $qurey->whereIn('id', $supplierIds);

        $supplierObjects = $qurey->get();
        if (empty($supplierObjects)) {
            return $list;
        }
        $suppliers = $supplierObjects->toArray();
        $supplierArr = [];
        foreach ($suppliers as $supplier) {
            $supplierArr[$supplier['id']] = $supplier['name'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($supplierArr[$val[$field]])) {
                $val[$fieldKey] = $supplierArr[$val[$field]];
            }
        }
    }

    public function getStatusList() {
        $status_list = [Supplier::STATUS_DRAFT, Supplier::STATUS_APPROVING, Supplier::STATUS_APPROVED, Supplier::STATUS_CLOSED];
        $result = [];
        foreach ($status_list as $status) {
            $result[] = ['name' => $this->getStatusText($status), 'value' => $status];
        }
        return $result;
    }

    public function manage() {
        $supplierId = (new SupplierAuditRepo())->getSupplierId();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = $this->model->getTable();
        $user = (new User)->getTable();
        $query = $this->model
                ->selectRaw('u.realname,u.phone,u.email,s.supplier_no,s.registered_at,s.id,s.name')
                ->from($supplier . ' as s')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('s.id', 'us.supplier_id')
                    ->where('us.is_manager', 1)
                    ->where('us.deleted_flag', 'N');
                })
                ->join($user . ' as u', function($join) {
                    $join->on('u.user_id', 'us.user_id')
                    ->where('u.deleted_flag', 'N');
                })
                ->where('s.id', $supplierId);
        return $query->first();
    }

    public function getEnterpriseList() {
        return ['ENTERPRISE' => '企业',
            'STATE_ORGANS' => '国家机关',
            'PUBLIC_INSTITUTIONS' => '事业单位',
            'SOCIAL_GROUPS' => '社会团体',
            'OTHER_ORGANIZATIONAL_STRUCTURES' => '其他组织机构',
            'INDIVIDUAL_BUSINESSES' => '个体户',
            'NATURAL_PERSON' => '自然人'
        ];
    }

    public function company(Request $request) {
        return '';
    }

    public function enterpriseType($regType) {
        if (strpos('有限责任公司', $regType) !== false) {
            return 'ENTERPRISE';
        } elseif (strpos('机关', $regType) !== false) {
            return 'STATE_ORGANS';
        } elseif (strpos('事业单位', $regType) !== false) {
            return 'PUBLIC_INSTITUTIONS';
        } elseif (strpos('团体', $regType) !== false) {
            return 'SOCIAL_GROUPS';
        } elseif (strpos('个体', $regType) !== false) {
            return 'INDIVIDUAL_BUSINESSES';
        } elseif (strpos('自然人', $regType) !== false) {
            return 'NATURAL_PERSON';
        } else {
            return '';
        }
    }

}
