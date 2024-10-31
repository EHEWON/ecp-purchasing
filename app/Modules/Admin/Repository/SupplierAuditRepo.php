<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Message,
    MessageReceiver,
    RoleUsers,
    Roles,
    Supplier,
    SupplierAudit,
    User,
    UserSupplier
};
use App\Modules\Admin\Repository\SendLogRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Lang,
    Redis
};

class SupplierAuditRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierAudit();
        parent::__construct($this->model);
    }

    public function updateData(int $supplierId, Request $request, string $auditType = 'CREATE') {

        switch ($auditType) {
            case 'CHANGE':
                SupplierAudit::insert([
                    'base' => json_encode([
                        'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                        'supplier_group_id' => !empty($request->supplier_group_id) ? intval($request->supplier_group_id) : '1',
                        'name' => trim($request->name),
                        'enterprise_type' => !empty($request->enterprise_type) ? $request->enterprise_type : '',
                        'profile' => !empty($request->profile) ? $request->profile : '',
                        'reg_capital' => !empty($request->reg_capital) ? $request->reg_capital : '',
                        'social_credit_code' => !empty($request->social_credit_code) ? $request->social_credit_code : '',
                        'legal_representative' => !empty($request->legal_representative) ? $request->legal_representative : '',
                        'scope_of_operation' => !empty($request->scope_of_operation) ? $request->scope_of_operation : '',
                        'remarks' => !empty($request->remarks) ? $request->remarks : '',
                        'address' => !empty($request->address) ? $request->address : '',
                    ]),
                    'supplier_id' => $supplierId,
                    'audit_type' => $auditType,
                    'attachs' => json_encode($request->attach),
                    'banks' => json_encode($request->bank),
                    'contacts' => json_encode($request->contact),
                    'status' => $request['status'] == 'DRAFT' ? SupplierAudit::STATUS_DRAFT : SupplierAudit::STATUS_PENDING,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                break;
            case 'SUBMIT':
                $count = SupplierAudit::where('supplier_id', $supplierId)
                        ->where('audit_type', 'SUBMIT')
                        ->count();
                if ($count > 0) {
                    return;
                }
                $audiId = SupplierAudit::insert([
                            'supplier_id' => $supplierId,
                            'audit_type' => $auditType,
                            'status' => SupplierAudit::STATUS_PASS,
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                $supplierName = trim($request->name);
                $bossUrl = env('BOSS_URL');
                $messageId = Message::insertGetId([
                            'receiver_type' => 'PURCHASER',
                            'content_url' => $bossUrl . '/front/#/supplierManage/supplierDetail?id=' . $supplierId . '&dataType=audit&audit_id=' . $audiId,
                            'sender_id' => $supplierId,
                            'message_type' => 'SYSTEM',
                            'message_title' => '【' . env('APP_NAME') . '】【' . $supplierName . '】已提交企业准入申请，请尽快处理。',
                            'message' => '【' . $supplierName . '】已正式提交企业准入信息，请尽快登录系统完成供应商准入。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                $request = new Request();
                $request->merge(['route' => '/admin/supplier/audit/verify',
                    'purchaser_id' => $request->purchaser_id]);
                $userList = (new UserRepo)->persons($request);
                if (empty($userList)) {
                    $dataList = [];
                    foreach ($userList as $user) {
                        $dataList[] = [
                            'message_id' => $messageId,
                            'receiver_id' => $user['user_id'],
                            'supplier_id' => $supplierId,
                            'org_id' => $request->purchaser_id,
                            'read_flag' => 'N',
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                    }
                    if (!empty($dataList)) {
                        MessageReceiver::insert($dataList);
                    }
                }
                break;
            case 'ACCESS':
                SupplierAudit::insert([
                    'supplier_id' => $supplierId,
                    'audit_type' => 'ACCESS',
                    'purchaser_id' => $request->purchaser_id,
                    'status' => SupplierAudit::STATUS_PASS,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                break;
            default :
                return SupplierAudit::insertGetId([
                            'supplier_id' => $supplierId,
                            'audit_type' => $auditType,
                            'status' => SupplierAudit::STATUS_PENDING,
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                break;
        }
    }

    public function comments(Request $request) {
        $admin = Auth::guard('admin')->user();
        if ($admin->user_type !== 'SUPPLIER' && empty($request->supplier_id)) {
            check(false, '供应商ID不能为空');
        } elseif ($admin->user_type == 'SUPPLIER') {
            $supplierId = $this->getSupplierId();
        } else {
            $supplierId = $request->supplier_id;
        }
        if (empty($supplierId)) {
            return ['data' => [], 'total' => 0];
        }
        $userTable = (new User())->getTable();
        $auditModel = new SupplierAudit();
        $query = $auditModel->selectRaw('a.status,a.audit_type,a.user_id,a.remark,'
                        . 'a.created_at,a.updated_at,a.audit_at,s.supplier_no,a.supplier_id,'
                        . 'if(u.realname="" or isnull(u.realname),u.username,u.realname) as user_name')
                ->from($auditModel->getTable() . ' as a')
                ->leftJoin($userTable . ' as u', function ($join) {
                    $join->on('u.user_id', '=', 'a.user_id')
                    ->where('u.deleted_flag', 'N');
                })
                ->join('supplier as s', 'a.supplier_id', '=', 's.id')
                ->where('a.deleted_flag', 'N')
                ->where('s.deleted_flag', 'N')
                ->whereIN('a.audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                ->where('a.supplier_id', $supplierId)
                ->orderBy('a.created_at', 'desc');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $list['total'] = $total;
        $list['data'] = $object->toArray();
        foreach ($list['data'] as &$item) {
            $item['audit_type_txt'] = $this->getType($item['audit_type']);
            $item['status_txt'] = $this->getStatus($item['status']);
        }
        return $list;
    }

    public function history(Request $request) {
        $supplierId = $this->getSupplierId();
        if (empty($supplierId)) {
            return ['data' => [], 'total' => 0];
        }
        $userTable = (new User())->getTable();
        $auditModel = new SupplierAudit();
        $query = $auditModel->selectRaw('a.status,a.audit_type,a.user_id,a.remark,s.supplier_no,a.supplier_id,'
                        . 'a.created_at,a.updated_at,a.audit_at,if(u.realname="" or isnull(u.realname),u.username,u.realname) as user_name')
                ->from($auditModel->getTable() . ' as a')
                ->leftJoin($userTable . ' as u', function ($join) {
                    $join->on('u.user_id', '=', 'a.user_id')
                    ->where('u.deleted_flag', 'N');
                })
                ->join('supplier as s', 'a.supplier_id', '=', 's.id')
                ->where('a.deleted_flag', 'N')
                ->where('s.deleted_flag', 'N')
                ->whereIN('a.audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                ->where('a.supplier_id', $supplierId)
                ->orderBy('a.created_at', 'desc');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $list['total'] = $total;
        $list['data'] = $object->toArray();
        foreach ($list['data'] as &$item) {
            $item['audit_type_txt'] = $this->getType($item['audit_type']);
            $item['status_txt'] = $this->getStatus($item['status']);
        }
        return $list;
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy('a.' . $sort, $order);
        if ($sort !== 'created_at') {
            $query->orderBy('ccreated_at', 'DESC');
        }
    }

    public function getType(string $auditType) {

        switch ($auditType) {
            case 'CHANGE':
                return '变更审核';
            case 'CREATE':
                return '创建审核';
            case 'SUBMIT':
                return '提交企业信息';
            case 'FREEZE':
                return '冻结审核';
            case 'UNFREEZE':
                return '解冻审核';
            case 'ACCESS':
                return '准入审核';
        }
    }

    public function getStatus(string $status) {
        switch ($status) {
            case 'REJECTED':
                return '审核未通过';
            case 'PASS':
                return '审核通过';
            case 'REVIEW':
                return '待审核';
            case 'DRAFT':
                return '草稿';
        }
    }

    public function setLastAuditBySupplier(&$supplier, string $field = 'id') {
        $supplier['audit'] = '';
        $supplier['audit'] = SupplierAudit::where('supplier_id', $supplier[$field])
                ->where('deleted_flag', 'N')
                ->orderBy('created_at', 'desc')
                ->value('status');
    }

    public function setLastAuditBySuppliers(&$supplierList, string $field = 'id') {
        if (empty($supplierList)) {
            return;
        }
        $supplierIds = [];
        foreach ($supplierList as &$supplier) {
            $supplier['audit'] = '';
            $supplierIds[] = $supplier[$field];
        }
        $table = $this->model->getTable();
        $qurey = CustomerAudit::selectRaw('a.status,a.supplier_id')
                ->from($table . ' as a')
                ->whereIn('a.supplier_id', $supplierIds)
                ->where('a.deleted_flag', 'N')
        ;
        $qurey->where(function ($q) use($table) {
            $q->whereRaw('1>(SELECT count(*)FROM ' . $table . ' as ta '
                    . '  WHERE ta.deleted_flag=\'N\' '
                    . ' AND  ta.supplier_id=a.supplier_id '
                    . ' AND ta.created_at>=a.created_at '
                    . ' ORDER BY ta.deleted_flag desc)');
        });
        $auditObject = $qurey->orderBy('a.created_at', 'desc')->get();
        if (empty($auditObject)) {
            return [];
        }
        $auditList = $auditObject->toArray();
        $auditArr = [];
        foreach ($auditList as $val) {
            $auditArr[$val['supplier_id']] = $val['status'];
        }
        foreach ($supplierList as &$supplier) {
            if (!empty($auditArr[$supplier['id']])) {
                $supplier['audit'] = $auditArr[$supplier['id']];
            }
        }
    }

    public function getUserEmail($agentId) {
        return User::where('user_id', $agentId)
                        ->where('deleted_flag', 'N')
                        ->whereIn('user_type', ['PURCHASER', 'PLATFORM'])
                        ->value('email');
    }

    public function getSupplierUserEmail($supplierId) {
        $userSupplier = (new UserSupplier)->getTable();
        $user = (new User)->getTable();
        return UserSupplier::from($userSupplier . ' as us')
                        ->join($user . ' as u', function($join) {
                            $join->on('us.user_id', 'u.user_id');
                        })
                        ->where('us.supplier_id', $supplierId)
                        ->where('us.deleted_flag', 'N')
                        ->where('u.deleted_flag', 'N')
                        ->where(['us.is_manager' => '1'])
                        ->where('u.user_type', 'SUPPLIER')
                        ->value('email');
    }

    /**
     * 审核
     *
     * @return array
     */
    public function verify(Request $request) {
        $auditId = $request->post('audit_id');
        $supplierId = $request->post('id');
        if (empty($auditId) && empty($supplierId)) {
            check(false, '供应商ID和供应商审核ID不能都为空');
        }
        $status = $request->post('status');
        $remark = $request->post('remark');
        if (!empty($supplierId)) {
            $audit = SupplierAudit::lockForUpdate()
                    ->where('deleted_flag', 'N')
                    ->where('status', 'REVIEW')
                    ->whereIn('audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                    ->where('supplier_id', $supplierId)
                    ->first();
            check(!empty($audit), Lang::get('response.no_data'));
            $auditId = $audit['id'];
        } else {
            $audit = SupplierAudit::lockForUpdate()
                    ->where('deleted_flag', 'N')
                    ->whereIn('audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                    ->where('status', 'REVIEW')
                    ->where('id', $auditId)
                    ->first();
            check(!empty($audit), Lang::get('response.no_data'));
            $supplierId = $audit['supplier_id'];
        }
        check($audit->status == Supplier::AUDIT_STATUS_AUDIT, Lang::get('supplier_audit.text_reviewed'));
        $supplier = Supplier::where('deleted_flag', 'N')->find($audit['supplier_id']);
        check(!empty($supplier), Lang::get('supplier_audit.message_store_not_exist'));
        $admin = Auth::guard('admin')->user();
        SupplierAudit::where('id', $auditId)->update([
            'user_id' => $admin->user_id,
            'remark' => $remark,
            'audit_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => $status == 'PASS' ? 'PASS' : 'REJECTED',
        ]);
        $bossUrl = env('BOSS_URL');
        switch ($audit->audit_type) {
            case 'CREATE':
                $flag = Supplier::where('id', $supplierId)->update([
                    'status' => $status == 'PASS' ? 'APPROVED' : 'INVALID',
                    'enable' => $status == 'PASS' ? 1 : null,
                ]);
                $roleId = Roles::where('role_no', 'GYS001')
                        ->where('deleted_flag', 'N')
                        ->value('id');
                $userIds = UserSupplier::where('supplier_id', $supplierId)
                        ->where('deleted_flag', 'N')
                        ->pluck('user_id');
                if (empty($roleId) || empty($userIds)) {
                    return $flag;
                }
                foreach ($userIds->toArray() as $userId) {
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
                $email = $this->getSupplierUserEmail($supplierId);
                if (empty($email)) {
                    return $flag;
                }
                $status === 'PASS' ? (new SendLogRepo)->supplierPass($email, $supplierId) //审核通过
                                : (new SendLogRepo)->supplierRefuse($email, $supplierId); //审核拒绝
                $messageId = Message::where('content_id', $supplierId)
                        ->where('content_operate', 'SUPPLIER_AUDIT')
                        ->orderBy('id', 'DESC')
                        ->value('id');
                if (!empty($messageId)) {
                    MessageReceiver::where('message_id', $messageId)->update(['read_flag' => 'Y']);
                }
                if ($status !== 'PASS') {
                    return $flag;
                }
                return $flag;
            case 'CHANGE':
                $email = $this->getSupplierUserEmail($supplierId);
                $messageId = Message::where('content_id', $supplierId)
                        ->where('content_operate', 'SUPPLIER_AUDIT')
                        ->orderBy('id', 'DESC')
                        ->value('id');
                if (!empty($messageId)) {
                    MessageReceiver::where('message_id', $messageId)->update(['read_flag' => 'Y']);
                }
                switch ($status) {
                    case 'PASS':
                        $fRequest = new Request();
                        $base = json_decode($audit['base'], true);
                        $base['attach'] = json_decode($audit['attachs'], true);
                        $base['bank'] = json_decode($audit['banks'], true);
                        $base['bank'] = json_decode($audit['banks'], true);
                        $base['contact'] = json_decode($audit['contacts'], true);
                        $fRequest->merge($base);
                        Supplier::where('id', $supplierId)->update([
                            'updated_at' => date('Y-m-d H:i:s'),
                            'supplier_group_id' => !empty($base['supplier_group_id']) ? intval($base['supplier_group_id']) : '1',
                            'enable' => 1,
                            'status' => 'APPROVED',
                            'name' => trim($base['name']),
                            'enterprise_type' => !empty($base['enterprise_type']) ? $base['enterprise_type'] : '',
                            'profile' => !empty($base['profile']) ? $base['profile'] : '',
                            'reg_capital' => !empty($base['reg_capital']) ? $base['reg_capital'] : '',
                            'social_credit_code' => !empty($base['social_credit_code']) ? $base['social_credit_code'] : '',
                            'legal_representative' => !empty($base['legal_representative']) ? $base['legal_representative'] : '',
                            'scope_of_operation' => !empty($base['scope_of_operation']) ? $base['scope_of_operation'] : '',
                            'remarks' => !empty($base['remarks']) ? $base['remarks'] : '',
                            'address' => !empty($base['address']) ? $base['address'] : '',
                        ]);
                        (new SupplierContactRepo)->updateData($supplierId, $fRequest);
                        (new SupplierBankRepo)->updateData($supplierId, $fRequest);
                        (new SupplierAttachRepo)->updateData($supplierId, $fRequest);
                        !empty($email) ? (new SendLogRepo)->supplierChangePass($email, $supplierId) : null;
                        $this->sendMessage('企业信息变更审核已通过', '您提交的企业认证审核已通过'.(!empty($remark)?'，【' . $remark . '】':'').'。', $bossUrl . '/front/#/supplierClient/companyInfo', $supplierId);
                        return [];
                    case 'REJECTED':
                        $flag = !empty($email) ? (new SendLogRepo)->supplierChangeRefuse($email, $supplierId) : null;
                        $this->sendMessage('企业信息变更审核未通过', '您提交的企业认证审核未通过'.(!empty($remark)?'，【' . $remark . '】':'').'。', $bossUrl . '/front/#/supplierClient/companyInfo', $supplierId);
                        return $flag;
                }
        }
    }

    public function sendMessage($messageTitle, $message, $contentUrl, $supplierId) {
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = 0;
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }

        $messageId = Message::insertGetId([
                    'receiver_type' => 'SUPPLIER',
                    'content_url' => $contentUrl,
                    'sender_id' => $curId,
                    'message_type' => 'SYSTEM',
                    'message_title' => $messageTitle,
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $userIdList = UserSupplier::where('deleted_flag', 'N')->where('supplier_id', $supplierId)->pluck('user_id');
        $dataList = [];
        foreach ($userIdList as $userId) {
            $dataList[] = [
                'message_id' => $messageId,
                'receiver_id' => $userId,
                'supplier_id' => $supplierId,
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (empty($dataList)) {
            return $messageId;
        }
        MessageReceiver::insert($dataList);
        return $messageId;
    }

    /**
     * 审核
     *
     * @return array
     */
    public function audit(Request $request) {
        $supplierId = $request->post('id');
        $status = $request->post('status');
        $remark = $request->post('remark');
        $audit = SupplierAudit::lockForUpdate()
                ->where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('status', 'REVIEW')
                ->where('audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                ->orderBy('created_at', 'desc')
                ->first();
        check(!empty($audit), Lang::get('response.no_data'));
        $nrequest = new Request();
        $nrequest->setMethod('POST');
        $nrequest->merge([
            'id' => $audit->id,
            'status' => $status,
            'remark' => $remark,
        ]);
        return $this->verify($nrequest);
    }

    /**
     * 待审核数量
     * @param  Request $request
     * @return
     */
    public function todo(Request $request) {
        $query = SupplierAudit::select('1')
                ->where('status', Supplier::AUDIT_STATUS_AUDIT)
                ->where('deleted_flag', 'N');
        return $query->count();
    }

    public function getSupplierId() {
        $user = Auth::guard('admin')->user();
        return UserSupplier::where('user_id', $user->user_id)->value('supplier_id');
    }

    /**
     * 供应商代办数量
     * @param  Request $request
     * @return
     */
    public function todoCount() {
        $supplierId = $this->getSupplierId();
        if (empty($supplierId)) {
            return ['enterprise_count' => 0, 'todo_count' => 0];
        }
        $enterpriseCount = Supplier::where('id', $supplierId)
                ->where('deleted_flag', 'N')
                ->whereIn('status', [Supplier::STATUS_DRAFT, Supplier::STATUS_REVIEW, Supplier::STATUS_INVALID])
                ->count();
        $todoCount = SupplierAudit::select('1')
                ->where('status', Supplier::AUDIT_STATUS_AUDIT)
                ->where('supplier_id', $supplierId)
                ->where('deleted_flag', 'N')
                ->count();
        return ['enterprise_count' => $enterpriseCount, 'todo_count' => $todoCount];
    }

    /**
     * 供应商代办数量
     * @param  Request $request
     * @return
     */
    public function progress() {
        $supplierId = $this->getSupplierId();
        if (empty($supplierId)) {
            return ['registration' => null,
                'enterprise' => null,
                'review' => null,
                'complete' => null,
                'status' => null,
                'audit_status' => null,
            ];
        }
        $supplier = Supplier::where('id', $supplierId)
                ->where('deleted_flag', 'N')
                ->first();
        if (empty($supplier)) {
            return ['registration' => null,
                'enterprise' => null,
                'review' => null,
                'complete' => null,
                'status' => null,
                'audit_status' => null,
            ];
        }
        $audit = SupplierAudit::where('deleted_flag', 'N')
                ->where('supplier_id', $supplierId)
                ->where('audit_type', 'CREATE')
                ->orderBy('created_at', 'desc')
                ->first();
        return ['registration' => $supplier->registered_at,
            'enterprise' => $supplier->filled_at,
            'review' => !empty($supplier->checked_at) ? $supplier->checked_at : (!empty($audit) ? $audit->audit_at : null),
            'complete' => !empty($audit) && $audit->status === 'PASS' ? $audit->audit_at : null,
            'status' => $supplier->status,
            'audit_status' => !empty($audit) ? $audit->status : null,
        ];
    }

    /**
     * 询单列表数据
     * @param  Request $request
     * @return array
     */
    public function pending(Request $request) {

        $query = $this->model
                ->from($this->model->getTable() . ' as a')
                ->select('a.id', 'a.audit_type', 'a.supplier_id', 's.supplier_no', 'a.created_at', 's.supplier_no', 's.name', 'a.status')
                ->where('a.status', SupplierAudit::STATUS_PENDING)
                ->where('a.audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                ->where('a.deleted_flag', 'N');
        $query->join('supplier as s', 'a.supplier_id', '=', 's.id');
        $total = $query->clone()->select('1')->count();
        $query->where('s.deleted_flag', 'N');
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $data = $query->get()->toArray();
        foreach ($data as &$item) {
            $item['audit_type_name'] = $this->getAuditType($item['audit_type']);
            $item['status_name'] = $this->getStatus($item['status']);
        }
        return ['total' => $total, 'data' => $data];
    }

    public function pendingTotal() {
        $scope = $this->getScopes('/admin/supplier/audit/verify');
        $query = $this->model
                ->from($this->model->getTable() . ' as a')
                ->where('a.status', SupplierAudit::STATUS_PENDING)
                ->where('a.deleted_flag', 'N');
        $query
                ->join('supplier as s', 'a.supplier_id', '=', 's.id')
                ->where('s.deleted_flag', 'N')
                ->where('a.audit_type', ['UNFREEZE', 'FREEZE', 'CHANGE', 'SUBMIT', 'CREATE'])
                ->where('s.deleted_flag', 'N');
        $query->where();
        if (empty($scope['scopes'])) {
            $query->whereRaw('1=-1');
        }
        $userId = $scope['user_id'];
        $teamId = $scope['team_id'];
        if (in_array('ALL', $scope['scopes'])) {
            return $query->count();
        }
        if (in_array('DEPARTMENT', $scope['scopes'])) {
            $query->where('s.purchaser_id', $teamId);
            return $query->count();
        }
        $query->where('s.purchaser_id', $teamId);
        $query->where(function($q)use($userId) {
            $q->where('s.created_by', $userId);
        });

        return $query->count();
    }

    public function getAuditType($auditType) {
        switch ($auditType) {
            case 'UNFREEZE':
                return '解冻审核';
            case 'FREEZE':
                return '冻结审核';
            case 'CHANGE':
                return '变更审核';
            case 'CREATE':
                return '新建审核';
            case 'SUBMIT':
                return '提交审核';
        }
    }

    /**
     * 
     */
    public function setAudits(array &$list, $filed = 'supplier_id') {
        if (empty($list)) {
            return;
        }
        $supplierIds = [];
        foreach ($list as &$val) {
            $val['audit_change'] = null;
            $val['audit_change_id'] = null;
            $supplierIds[] = $val[$filed];
        }
        if (empty($supplierIds)) {
            return;
        }
        $qurey = $this->model->selectRaw('id,supplier_id,status,audit_type');
        $qurey->whereIn('supplier_id', $supplierIds)
                ->whereIn('audit_type', ['CHANGE', 'CREATE', 'SUBMIT'])
                ->where('status', 'REVIEW');
        $supplierObjects = $qurey->get();
        if (empty($supplierObjects)) {
            return;
        }
        $suppliers = $supplierObjects->toArray();
        if (empty($suppliers)) {
            return;
        }
        $supplierArr = [];
        foreach ($suppliers as $supplier) {
            $supplierArr[$supplier['supplier_id']] = $supplier;
        }
        foreach ($list as &$val) {
            if ($val[$filed] && !isset($supplierArr[$val[$filed]])) {
                continue;
            }
            $supplier = $supplierArr[$val[$filed]];
            $val['audit_change'] = $supplier['status'];
            $val['audit_change_id'] = $supplier['id'];
        }
        return;
    }

    /**
     * 
     */
    public function setAudit(&$val, $filed = 'supplier_id') {
        if (empty($val)) {
            return;
        }
        $val['audit_change'] = null;
        $val['audit_change_id'] = null;
        $supplierId = $val[$filed];
        $qurey = $this->model->selectRaw('id,supplier_id,status,audit_type');
        $qurey->where('supplier_id', $supplierId)
                ->whereIn('audit_type', ['CHANGE', 'CREATE', 'SUBMIT'])
                ->where('status', 'REVIEW');
        $supplierObjects = $qurey->get();
        if (empty($supplierObjects)) {
            return;
        }
        $suppliers = $supplierObjects->toArray();
        $supplierArr = [];
        foreach ($suppliers as $supplier) {
            $supplierArr[$supplier['supplier_id']] = $supplier;
        }
        if (empty($supplierArr[$val[$filed]])) {
            return;
        }
        $supplier = $supplierArr[$val[$filed]];
        $val['audit_change'] = $supplier['status'];
        $val['audit_change_id'] = $supplier['id'];
        return;
    }

}
