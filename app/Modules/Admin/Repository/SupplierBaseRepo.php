<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Message,
    MessageReceiver,
    RoleUsers,
    Roles,
    SendLog,
    Supplier,
    SupplierAudit,
    SupplierContact,
    User,
    UserContact,
    UserSupplier
};
use App\Modules\Admin\Mail\{
    SupplierAccountCreated,
    VerifyMail
};
use App\Modules\Admin\Repository\{
    SupplierAttachRepo,
    SupplierAuditRepo,
    SupplierBankRepo,
    SupplierContactRepo,
    OrgRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Mail,
    Redis
};
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class SupplierBaseRepo extends Repository {

    protected $model;
    protected $sorts = [
        'number',
        'name',
        'enable',
        'disabled_at',
        'created_at',
    ];

    const INVALID_STATUS = ['注销', '停业', '清算'];

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
            if ($item['status'] !== 'APPROVED') {
                $item['enable'] = null;
            }
            $item['enable_name'] = $this->getEnableText($item['enable']);
            $item['enterprise_type_name'] = $this->getEnterpriseTypeText($item['enterprise_type']);
        }
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        (new OrgRepo)->setOrgs($data, 'purchaser_id', 'purchaser_name');
        (new SupplierAuditRepo)->setAudits($data, 'id');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getTotal(Request $request) {
        $query = Supplier::where('deleted_flag', 'N');
        return $query->count();
    }

    public function pendingTotal() {
        $query = Supplier::where('deleted_flag', 'N');
        $audit = (new SupplierAudit)->getTable();
        $query->where(function($q)use($audit) {
            $q->where('status', 'APPROVING')
                    ->orWhereRaw('(status=\'APPROVED\' AND EXISTS(SELECT sa.id FROM ' . $audit
                            . '  as sa where sa.supplier_id=supplier.id '
                            . ' AND sa.deleted_flag=\'N\' '
                            . ' AND sa.status=\'REVIEW\''
                            . 'AND sa.audit_type=\'CHANGE\'))');
        });
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function suppliers(Request $request) {
        $query = $this->model
                ->selectRaw('id,name,supplier_no,enable,status,purchaser_id,source');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('supplier_no', 'like', '%' . $keyword . '%');
            });
        }
        $query->where('enable', 1);
        $query->where('status', 'APPROVED');
        $query->where('deleted_flag', 'N');

        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['source_name'] = $this->getSourceText($item['source']);
            $item['status_name'] = $this->getStatusText($item['status']);
            if ($item['status'] !== 'APPROVED') {
                $item['enable'] = null;
            }
            $item['enable_name'] = $this->getEnableText($item['enable']);
        }
        (new SupplierContactRepo)->setDefaultContacts($data, 'id');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $id
     * @param string $request
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
        (new OrgRepo)->setOrg($data, 'purchaser_id', 'purchaser_name');
        (new SupplierAuditRepo)->setAudit($data, 'id');
        (new SupplierGroupRepo())->setGroup($data);
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
            $audit = SupplierAudit::where('supplier_id', $supplierId)
                    ->where('status', SupplierAudit::STATUS_DRAFT)
                    ->first();
            $auditData = [
                'base' => json_encode([
                    'supplier_no' => $supplier->supplier_no,
                    'enable' => $supplier->enable,
                    'source' => $supplier->source,
                    'filled_at' => $supplier->filled_at,
                    'registered_at' => $supplier->registered_at,
                    'industry_classification' => $supplier->industry_classification,
                    'purchaser_id' => 1,
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
            'purchaser_id' => !empty($request->purchaser_id) ? intval($request->purchaser_id) : 1,
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
                        'message' => '【' . $supplierData['name'] . '】已提交企业信息认证。',
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
            !empty($emails) ? (new SendLogRepo())->supplierSubmit($emails, trim($request->name), $supplierId) : null;
        }
        return $flag;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        $status = $request->status;
        $supplierData = [
            'source' => $admin->user_type === 'PLATFORM' ? 'BOSS' : 'PURCHASER',
            'created_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'purchaser_id' => !empty($request->purchaser_id) ? intval($request->purchaser_id) : 1,
            'registered_at' => date('Y-m-d H:i:s'),
            'number' => trim($request->number),
            'supplier_group_id' => !empty($request->supplier_group_id) ? intval($request->supplier_group_id) : '1',
            'enable' => !empty($request->enable) ? intval($request->enable) : 1,
            'status' => !empty($status) ? $status : 'REVIEW',
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
            $supplierData['supplier_no'] = $this->getSupplierNo();
        }
        $supplierId = Supplier::insertGetId($supplierData);
        (new SupplierContactRepo)->updateData($supplierId, $request);
        (new SupplierBankRepo)->updateData($supplierId, $request);
        (new SupplierAttachRepo)->updateData($supplierId, $request);
        if ($status !== 'DRAFT' && $status !== Supplier::STATUS_REVIEW) {
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
                        'message' => '【' . $supplierData['name'] . '】已提交企业信息认证。',
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
            !empty($emails) ? (new SendLogRepo())->supplierSubmit($emails, trim($request->name), $supplierId) : null;
        }
        if (!empty($request->phone_email) && !empty($request->realname)) {
            $request->merge(['password' => 'ecp@2024']);
            $this->insertUser($request, $supplierId);
            if (isEmail(trim($request->phone_email))) {
                Mail::to(trim($request->phone_email))
                        ->send(new SupplierAccountCreated(['phone_email' => trim(trim($request->phone_email)),
                            'phone' => '']));
            }
        }
        return $supplierId;
    }

    public function auditInfo(int $supplierId, Request $request) {
        $data = $this->getLastAudit($supplierId);
        if (!empty($data)) {
            return $data;
        }
        return $this->info($supplierId, $request);
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
        $audit = $auditObj->toArray();
        $data = json_decode($audit['base'], true);
        $data['id'] = (string) $supplierId;
        $data['audit_change'] = $audit['status'];
        $data['audit_change_id'] = (string) $audit['id'];
        $data['supplier_group_id'] = (string) $data['supplier_group_id'];
        $data['purchaser_id'] = (string) $data['purchaser_id'];
        $data['reg_capital'] = !empty(floatval($data['reg_capital'])) ? $data['reg_capital'] : null;
        $data['status'] = 'APPROVED';
        (new SupplierGroupRepo())->setGroup($data);
        $data['source_name'] = $this->getSourceText($data['source']);
        $data['status_name'] = $this->getStatusText($data['status']);
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
        return $data;
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
        $count = Supplier::whereIn('id', $ids)
                ->where('deleted_flag', 'N')
                ->count();
        $flag = Supplier::whereIn('id', $ids)
                ->where('deleted_flag', 'N')
                ->where('enable', 0)
                ->where('status', 'APPROVED')
                ->update(['enable' => 1,
            'disabled_by' => null,
            'disabled_at' => null,
            'updated_by' => $admin->user_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($flag === 0) {
            $str = '解冻全部失败';
        } elseif ($count > $flag) {
            $str = '解冻成功' . $flag . '条，失败' . ($count - $flag) . '条';
        } else {
            $str = '解冻全部成功';
        }
        return $str;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        $count = Supplier::whereIn('id', $ids)
                ->where('deleted_flag', 'N')
                ->count();
        $flag = Supplier::whereIn('id', $ids)
                ->where('deleted_flag', 'N')
                ->where('status', 'APPROVED')
                ->where('enable', 1)
                ->update([
            'enable' => 0,
            'disabled_by' => $admin->user_id,
            'disabled_at' => date('Y-m-d H:i:s'),
        ]);
        if ($flag === 0) {
            $str = '全部禁用失败';
        } elseif ($count > $flag) {
            $str = '禁用成功' . $flag . '条，失败' . ($count - $flag) . '条';
        } else {
            $str = '全部禁用成功';
        }
        return $str;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $count = Supplier::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->count();
        check($count !== 0, '已删除的供应商不用删除');
        $countN = Supplier::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->whereIn('status', ['APPROVING', 'APPROVED'])
                ->count();
        check($count !== $countN, '处于审核中或已审核的供应商不能删除');
        $countP = Supplier::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->whereIn('source', ['REGISTER'])
                ->count();
        check($count !== $countP, '前台注册的供应商不能删除');
        $supplierIds = Supplier::whereIn('id', $request->ids)
                ->where('deleted_flag', 'N')
                ->whereIn('source', ['PURCHASER', 'BOSS'])
                ->whereIn('status', ['DRAFT', 'REVIEW'])
                ->pluck('id');
        DB::beginTransaction();
        $flag = Supplier::whereIn('id', $supplierIds)->where('deleted_flag', 'N')->update(['deleted_flag' => 'Y']);
        $userIds = \App\Common\Models\UserSupplier::whereIn('supplier_id', $supplierIds)->pluck('user_id');
        \App\Common\Models\SupplierAttach::whereIn('supplier_id', $supplierIds)->update(['deleted_flag' => 'Y']);
        \App\Common\Models\SupplierAudit::whereIn('supplier_id', $supplierIds)->update(['deleted_flag' => 'Y']);
        \App\Common\Models\SupplierBank::whereIn('supplier_id', $supplierIds)->update(['deleted_flag' => 'Y']);
        SupplierContact::whereIn('supplier_id', $supplierIds)->update(['deleted_flag' => 'Y']);
        \App\Common\Models\UserSupplier::whereIn('supplier_id', $supplierIds)->update(['deleted_flag' => 'Y']);
        if (!empty($userIds)) {
            User::whereIn('user_id', $userIds)
                    ->where('deleted_flag', 'N')
                    ->where('user_type', 'SUPPLIER')
                    ->update(['deleted_flag' => 'Y']);
        }
        $str = '';
        if (!empty($flag)) {
            $str .= '删除成功' . $flag . '条，';
        }
        if (!empty($countN)) {
            $str .= (!empty($str) ? '，' : '') . '处于审核中或已审核不能删除的供应商' . $countN . '条';
        }
        if (!empty($countP)) {
            $str .= (!empty($str) ? '，' : '') . '前台注册不能删除的供应商' . $countN . '条';
        }
        DB::commit();
        check($count === $flag, $str);
        return '全部删除成功';
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('deleted_flag', 'N');
        $query->where(function($q) {
            $q->whereIn('status', ['APPROVING', 'APPROVED', 'INVALID', 'REVIEW'])
                    ->whereIn('source', ['BOSS', 'PURCHASER'])
                    ->orWhere(function($q1) {
                        $q1->whereIn('status', ['APPROVING', 'APPROVED', 'INVALID'])
                        ->where('source', 'REGISTER');
                    });
        });


        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $userSupplier = (new UserSupplier)->getTable();
            $user = (new User)->getTable();
            $query->where(function ($q)use($keyword, $userSupplier, $user) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('supplier_no', 'like', '%' . $keyword . '%')
                        ->orWhereExists(function($qe)use($user, $userSupplier, $keyword) {
                            $qe->selectRaw('u.user_id')
                            ->from($user . ' AS u')
                            ->join($userSupplier . ' AS us', function($join) {
                                $join->on('u.user_id', '=', 'us.user_id');
                            })
                            ->where('u.deleted_flag', 'N')
                            ->where('us.deleted_flag', 'N')
                            ->whereRaw('us.supplier_id=supplier.id')
                            ->where(function($qu)use($keyword) {
                                $qu->where('u.realname', 'like', '%' . $keyword . '%')
                                ->orWhere('u.email', 'like', '%' . $keyword . '%')
                                ->orWhere('u.phone', 'like', '%' . $keyword . '%');
                            });
                        });
            });
        }
        if (!empty($request->status) && $request->status === 'APPROVING') {
            $audit = (new SupplierAudit)->getTable();
            $query->where(function($q)use($audit) {
                $q->where('status', 'APPROVING')
                        ->orWhereRaw('(status=\'APPROVED\' AND EXISTS(SELECT sa.id FROM ' . $audit
                                . '  as sa where sa.supplier_id=supplier.id '
                                . ' AND sa.deleted_flag=\'N\' '
                                . ' AND sa.status=\'REVIEW\''
                                . 'AND sa.audit_type=\'CHANGE\'))');
            });
        } elseif (!empty($request->status) && $request->status === 'APPROVED') {
            $audit = (new SupplierAudit)->getTable();
            $query->where(function($q)use($audit) {
                $q->where('status', 'APPROVED')
                        ->whereRaw(' NOT EXISTS(SELECT sa.id FROM ' . $audit
                                . '  as sa where sa.supplier_id=supplier.id '
                                . ' AND sa.deleted_flag=\'N\' '
                                . ' AND sa.status=\'REVIEW\''
                                . 'AND sa.audit_type=\'CHANGE\'))');
            });
        } elseif (!empty($request->status)) {
            $status = $request->status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('status', $statusies);
        }
        if (!empty($request->source)) {
            $source = $request->source;
            switch ($source) {
                case 'BOSS':
                case 'REGISTER':
                    $query->whereIn('source', ['REGISTER', 'BOSS']);
                    break;
                case 'PURCHASER':
                    $query->where('source', 'PURCHASER');
                    break;
            }
        }
        if (!empty($request->statusies)) {
            $query->whereIn('status', $request->statusies);
        }
        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            $query->whereIn('enable', $enables);
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
        if ($request->type === 'ALL') {
            $query->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('deleted_flag', 'N')
                    ->whereIn('id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
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
        $sheet->mergeCells('A1:F1');
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
            $this->setExcelRow($sheet, 'D', $row, $item['enable_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['source_name'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['filled_at'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:F2')
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
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $token = Auth::guard('admin')->getToken();
        $curId = Redis::get(md5($token));
        $admin = Auth::guard('admin')->user();
        $userType = $admin->user_type;
        $adminUserId = $admin->user_id;
        $data = $this->dataTrim($importData);
        $list = [];
        foreach ($data as $v) {
            if (empty($v[1]) && empty($v[2]) && empty($v[3]) && empty($v[4]) && empty($v[5])) {
                continue;
            }
            $item['supplier_no'] = trim($v[0]);
            $item['name'] = trim($v[1]);
            $item['social_credit_code'] = trim($v[2]);
            $item['realname'] = trim($v[3]);
            $item['email'] = trim($v[4]);
            $item['phone'] = trim($v[5]);
            $item['created_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['updated_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素     
            $item['created_at'] = date('Y-m-d H:i:s'); //申报要素          
            $item['updated_at'] = date('Y-m-d H:i:s'); //申报要素     
            $item['source'] = $userType === 'PLATFORM' ? 'BOSS' : 'PURCHASER';
            $item['supplier_group_id'] = '1';
            $list[] = $item;
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        $errors = [];
        $roleId = Roles::where('role_no', 'GYS001')
                ->where('deleted_flag', 'N')
                ->value('id');
        foreach ($list as $key => $v) {
            DB::beginTransaction();
            $error = [];
            if (empty($v['name'])) {
                $error[] = '企业名称不能为空,';
            }
            if (empty($v['social_credit_code'])) {
                $error[] = '统一社会信用代码不能为空';
            }
            if (!empty($error)) {
                $errors[] = '第' . ($key + 1) . '行数据存在' . implode(',', $error) . '的错误' . PHP_EOL;
                DB::rollBack();
                continue;
            }
            if (empty($v['realname'])) {
                $error[] = '联系人不能为空,';
            }
            if (empty($v['email']) && empty($v['phone'])) {
                $error[] = '联系人电话和邮箱不能都为空,';
            }
            $id = null;
            if ($v['supplier_no']) {
                $supplierObj = Supplier::where('deleted_flag', 'N')
                        ->where('supplier_no', $v['supplier_no'])
                        ->where('deleted_flag', 'N')
                        ->selectRaw('purchaser_id,id')
                        ->first();
                if (!empty($supplierObj)) {
                    $id = $supplierObj->id;
                    $purchaserId = $supplierObj->purchaser_id;
                }
            } elseif (!empty($v['social_credit_code']) || !empty($v['name'])) {
                $supplierObj = Supplier::where('deleted_flag', 'N')
                        ->where(function($q)use($v) {
                            $q->where('social_credit_code', trim($v['social_credit_code']))
                            ->orWhere('name', trim($v['name']));
                        })
                        ->where('deleted_flag', 'N')
                        ->selectRaw('purchaser_id,id')
                        ->first();

                if (!empty($supplierObj)) {
                    $id = $supplierObj->id;
                    $error[] = '名称【' . $v['name'] . '】或统一社会信用代码【' . $v['social_credit_code'] . '】的供应商不存在,';
                }
            }
            $supplier = [];
            if (!empty($error)) {
                $errors[] = '供应商【' . $v['name'] . '】存在' . implode(',', $error) . '的错误' . PHP_EOL;
                DB::rollBack();
                continue;
            }
            if (empty($id)) {
                $supplierId = Supplier::insertGetId([
                            'status' => 'APPROVED',
                            'supplier_no' => !empty($v['supplier_no']) ? $v['supplier_no'] : $this->getSupplierNo(),
                            'social_credit_code' => trim($v['social_credit_code']),
                            'name' => trim($v['name']),
                            'purchaser_id' => $purchaserId,
                            'filled_at' => date('Y-m-d H:i:s'),
                            'registered_at' => date('Y-m-d H:i:s'),
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => !empty($supplier['created_by']) ? $supplier['created_by'] : 0,
                            'number' => $this->getNumber(),
                            'source' => $userType === 'PLATFORM' ? 'BOSS' : 'PURCHASER',
                            'supplier_group_id' => '1',
                            'enable' => 1,
                            'legal_representative' => !empty($supplier['legal_representative']) ? $supplier['legal_representative'] : '',
                            'reg_capital' => !empty($supplier['reg_capital']) ? $supplier['reg_capital'] : '',
                            'scope_of_operation' => !empty($supplier['scope_of_operation']) ? $supplier['scope_of_operation'] : '',
                            'enterprise_type' => !empty($supplier['enterprise_type']) ? $supplier['enterprise_type'] : '',
                            'address' => !empty($supplier['address']) ? $supplier['address'] : '',
                ]);
                SupplierAudit::insert([
                    'supplier_id' => $supplierId,
                    'audit_type' => 'CREATE',
                    'user_id' => $adminUserId,
                    'audit_at' => date('Y-m-d H:i:s'),
                    'status' => SupplierAudit::STATUS_PASS,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $contactNum = SupplierContact::where('supplier_id', $supplierId)->count();
                if (empty($contactNum)) {
                    SupplierContact::insertGetId([
                        'supplier_id' => $supplierId,
                        'phone' => !empty($v['phone']) ? trim($v['phone']) : '',
                        'contact_name' => !empty($v['realname']) ? trim($v['realname']) : '',
                        'email' => !empty($v['email']) ? trim($v['email']) : '',
                        'default_flag' => 'Y',
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $admin->user_id,
                    ]);
                }
            } elseif (!empty($id)) {
                $supplierStatus = Supplier::where('id', $id)->value('status');
                Supplier::where('id', $id)->update([
                    'status' => 'APPROVED',
                    'social_credit_code' => trim($v['social_credit_code']),
                    'name' => trim($v['name']),
                    'filled_at' => date('Y-m-d H:i:s'),
                    'registered_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'number' => $this->getNumber(),
                    'source' => $userType === 'PLATFORM' ? 'BOSS' : 'PURCHASER',
                    'supplier_group_id' => '1',
                    'enable' => 1,
                ]);
                $audit = SupplierAudit::lockForUpdate()
                        ->where('deleted_flag', 'N')
                        ->where('status', 'REVIEW')
                        ->where('supplier_id', $id)
                        ->first();
                $supplierId = $id;
                empty($audit) ? SupplierAudit::insert([
                                    'supplier_id' => $supplierId,
                                    'audit_type' => $supplierStatus === 'APPROVED' ? 'CHANGE' : 'CREATE',
                                    'user_id' => $adminUserId,
                                    'audit_at' => date('Y-m-d H:i:s'),
                                    'status' => SupplierAudit::STATUS_PASS,
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]) : SupplierAudit::where('id', $audit->id)->update([
                                    'status' => SupplierAudit::STATUS_PASS,
                                    'user_id' => $adminUserId,
                ]);
                $contactNum = SupplierContact::where('supplier_id', $supplierId)->count();
                SupplierContact::insertGetId([
                    'supplier_id' => $supplierId,
                    'phone' => !empty($v['phone']) ? trim($v['phone']) : '',
                    'contact_name' => !empty($v['realname']) ? trim($v['realname']) : '',
                    'email' => !empty($v['email']) ? trim($v['email']) : '',
                    'default_flag' => 'Y',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $admin->user_id,
                ]);
            }

            if (!empty($id)) {
                DB::commit();
                continue;
            }
            if (!empty($v['email'])) {
                $countN = User::where('email', $v['email'])
                        ->where('deleted_flag', 'N')
                        ->count();
                !empty($countN) ? $error[] = '邮箱账号已存在' : null;
            }
            if (!empty($v['phone'])) {
                $countP = User::where('phone', $v['phone'])
                        ->where('deleted_flag', 'N')
                        ->count();
                !empty($countP) ? $error[] = '手机账号已存在' : null;
            }
            if (!empty($error)) {
                $errors[] = '第' . ($key + 1) . '行,存在' . implode(',', $error) . '的错误' . PHP_EOL;
                DB::rollBack();
                continue;
            }
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
            $userContactNum = UserContact::where('user_id', $userId)->count();
            if (trim($v['email']) && empty($userContactNum)) {
                UserContact::insertGetId([
                    'user_id' => $userId,
                    'contact_type' => 'EMAIL',
                    'contact_value' => trim($v['email']),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            if (trim($v['phone']) && empty($userContactNum)) {
                UserContact::insertGetId([
                    'user_id' => $userId,
                    'contact_type' => 'PHONE',
                    'contact_value' => trim($v['phone']),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            if (!empty($roleId)) {
                RoleUsers::where('user_id', $userId)->delete();
                RoleUsers::insertGetId([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'team_id' => $supplierId,
                    'content_id' => $supplierId,
                    'role_group' => 'SUPPLIER',
                    'deleted_flag' => 'N',
                    'created_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            if (isEmail($v['email']) && !empty($v['email'])) {
                Mail::to($v['email'])
                        ->send(new SupplierAccountCreated(['phone_email' => $v['email'],
                            'phone' => $v['phone']]));
            }
            DB::commit();
        }
        check(empty($errors), implode(',', $errors));
        return true;
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
        $localFullFileName = $tmpSavePath . mb_convert_encoding(urldecode(basename($attach_name)), 'UTF-8', 'UTF-8');
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
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds;
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
        Redis::setex($request->post('register_supplier_code_' . $phoneEmail), $expiredSeconds, $code);
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
        $supplierObject = $qurey->where('deleted_flag', 'N')->first();
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
            $val['supplier_no'] = '';
            if (isset($val[$field]) && $val[$field]) {
                $supplierIds[] = $val[$field];
            }
        }

        if (empty($supplierIds)) {
            return $list;
        }
        $qurey = $this->model
                ->select('id', 'name', 'supplier_no');
        $qurey->whereIn('id', $supplierIds);

        $supplierObjects = $qurey->where('deleted_flag', 'N')->get();
        if (empty($supplierObjects)) {
            return $list;
        }
        $suppliers = $supplierObjects->toArray();
        $supplierArr = [];
        foreach ($suppliers as $supplier) {
            $supplierArr[$supplier['id']] = $supplier;
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($supplierArr[$val[$field]])) {
                $val[$fieldKey] = $supplierArr[$val[$field]]['name'];
                $val['supplier_no'] = $supplierArr[$val[$field]]['supplier_no'];
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

}
