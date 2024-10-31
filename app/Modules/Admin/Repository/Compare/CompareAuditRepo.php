<?php

namespace App\Modules\Admin\Repository\Compare;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Compare\Compare,
    Compare\CompareAudit,
    Compare\CompareQuote,
    Compare\Entry,
    Inquiry\Inquiry,
    Inquiry\Supplier AS InquirySupplier,
    Message,
    MessageReceiver,
    Quote\Quote,
    RoleUsers,
    Roles,
    User,
    UserSupplier
};
use App\Jobs\SendMailJob;
use App\Modules\Admin\Repository\{
    Compare\CompareQuoteRepo,
    Compare\CompareRepo,
    NoticeManageRepo,
    SendLogRepo,
    UserRepo
};
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Lang,
    Redis
};

class CompareAuditRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new CompareAudit();
        parent::__construct($this->model);
    }

    public function auditLogs($compareId) {
        $object = $this->model->where('compare_id', $compareId)->orderby('created_at', 'asc')->get();
        if (empty($object)) {
            return ['data' => []];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'user_id', 'user_name');
        $list = [];
        $list['data'] = $data;
        return $list;
    }

    public function auditStart($compareId, $auditData = []) {
        $compareData = Compare::where('id', $compareId)->first()->toArray();
        $admin = Auth::guard('admin')->user();
        if ($compareData['audit_flag'] == 'end' || $compareData['audit_flag'] == 'END') {//||
            check(false, '该流程已经审批完成');
        }
        $this->auditEnd($compareId, $compareData);
    }

    public function stop($compareId, $auditData = []) {
        $compareData = Compare::where('id', $compareId)->first()->toArray();
        $admin = Auth::guard('admin')->user();
        if ($compareData['audit_status'] == 'END') {
            check(false, '审核已完成无法终止');
        }
        if ($compareData['created_by'] == $admin->user_id) {
            $compareAuditData = [
                'compare_id' => $compareId,
                'compare_no' => $compareData['bill_no'],
                'user_id' => $admin->user_id,
                'status' => 'STOP',
                'remark' => $auditData['remark'],
                'audit_flag_name' => '提交审批',
                'audit_flag' => 'submit',
                'audit_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $admin->user_id,
            ];
            $compareAuditId = CompareAudit::insertGetId($compareAuditData);
            Compare::where('id', $compareId)->update(['bill_status' => 'A', 'audit_status' => '', 'audit_flag' => '', 'audit_by' => '', 'audit_flag_name' => '']);
            return $compareAuditId;
        } else {
            check(false, '非发起人不能终止');
        }
    }

    public function auditEnd($compareId, $compareData) {
        Compare::where('id', $compareId)->update(['bill_status' => 'C',
            'audit_status' => 'PASS',
            'audit_flag' => 'END',
            'audit_by' => '',
            'audit_flag_name' => '']);
        $compareData = Compare::where('id', $compareId)->first()->toArray();
        Inquiry::where('id', $compareData['inquiry_id'])
                ->update(['biz_status' => 'C']);
        $data = (new CompareRepo)->notice($compareId, $compareData['inquiry_id']);
        $quotes = (new CompareQuoteRepo)->getList($compareId);
        $purchaserName = \App\Common\Models\Purchaser::where('id', $compareData['org_id'])->value('name');
        $quotes_adopt_count = CompareQuote::where('compare_id', $compareId)
                ->where('adopt_flag', 'true')
                ->where('deleted_flag', 'N')
                ->count();
        foreach ($quotes as $item) {
            $person_id = \App\Common\Models\Quote\Quote::where('id', $item['quote_id'])
                    ->where('deleted_flag', 'N')
                    ->value('created_by');
            $adopt_entry_count = Entry::where('quote_id', $item['quote_id'])->where('adopt_flag', 'true')->where('deleted_flag', 'N')->count();
            $entry_count = Entry::where('quote_id', $item['quote_id'])->where('deleted_flag', 'N')->count();
            $bossUrl = env('BOSS_URL');
            if ($item['adopt_flag'] == 'true') {
                if ($quotes_adopt_count == 1 && $adopt_entry_count == $entry_count) {
                    Quote::where('id', $item['quote_id'])
                            ->where('deleted_flag', 'N')
                            ->update(['biz_status' => 'C']);
                    InquirySupplier::where('quote_id', $item['quote_id'])
                            ->update(['entry_status' => 'C']);
                } else {
                    Quote::where('id', $item['quote_id'])
                            ->where('deleted_flag', 'N')
                            ->update(['biz_status' => 'D']);
                    InquirySupplier::where('quote_id', $item['quote_id'])
                            ->update(['entry_status' => 'D']);
                }
                //$userSupplier = (new UserSupplier)->getTable();
                $email = User::where('user_id', $person_id)
                        ->where('deleted_flag', 'N')
                        ->where('user_type', 'SUPPLIER')
                        ->value('email');
                if ($email) {
                    $dataemail = (new CompareRepo)->notice($compareData['id'], $compareData['inquiry_id']);
                    app(Dispatcher::class)->dispatch(new SendMailJob([
                        'email' => $email,
                        'purchaserName' => $purchaserName,
                        'data' => $dataemail,
                        'inquiry_id' => $compareData['inquiry_id'],
                            ], 'COMPAREPASS'));
                }
                $messageId = Message::insertGetId([
                            'receiver_type' => 'SUPPLIER',
                            'content_url' => $bossUrl . '/front/#/quoteManage/quoteAssistant?id=' . $item['quote_id'] . '&type=info',
                            'sender_id' => $compareData['org_id'],
                            'message_type' => 'SYSTEM',
                            'message_title' => '【' . env('APP_NAME') . '】【' . $purchaserName . '】询价结果通知',
                            'message' => '询价标题：【' . $compareData['inquiry_title'] . '】，询价单号：【' . $compareData['inquiry_no'] . '】）的结果状态为【采纳结果】，请您查阅。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                messageReceiver::insertGetId([
                    'message_id' => $messageId,
                    'receiver_id' => $person_id,
                    'supplier_id' => $item['supplier_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                Quote::where('id', $item['quote_id'])
                        ->where('deleted_flag', 'N')
                        ->update(['biz_status' => 'E']);
                $email = User::where('user_id', $person_id)->where('deleted_flag', 'N')->where('user_type', 'SUPPLIER')->value('email');
                if ($email) {
                    // (new SendLogRepo)->CompareRefuse($email, $purchaserName, $compareData['inquiry_id']);
                    app(Dispatcher::class)->dispatch(new SendMailJob([
                        'email' => $email,
                        'purchaserName' => $purchaserName,
                        'inquiry_id' => $compareData['inquiry_id'],
                            ], 'COMPAREREFUSE'));
                }
                InquirySupplier::where('quote_id', $item['quote_id'])
                        ->update(['entry_status' => 'E']);
                $messageId = Message::insertGetId([
                            'receiver_type' => 'SUPPLIER',
                            'content_url' => $bossUrl . '/front/#/quoteManage/quoteAssistant?id=' . $item['quote_id'] . '&type=info',
                            'sender_id' => $compareData['org_id'],
                            'message_type' => 'SYSTEM',
                            'message_title' => '【' . env('APP_NAME') . '】【' . $purchaserName . '】询价结果通知',
                            'message' => '询价标题：【' . $compareData['inquiry_title'] . '】，询价单号：【' . $compareData['inquiry_no'] . '】）的结果状态为【未采纳结果】，请您查阅。',
                            'created_at' => date('Y-m-d H:i:s'),
                ]);
                messageReceiver::insertGetId([
                    'message_id' => $messageId,
                    'receiver_id' => $person_id,
                    'supplier_id' => $item['supplier_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $nrequest = (new Request);
        $nrequest->merge($data);
        (new NoticeManageRepo)->addData($nrequest);
    }

    public function auditRejected($compareId, $compareData) {
        //Inquiry::where('id', $base['inquiry_id'])->update(['biz_status' => 'C']);
        Compare::where('id', $compareId)->update(['bill_status' => 'A', 'audit_status' => 'REJECTED', 'audit_flag' => '', 'audit_by' => '', 'audit_flag_name' => '']);
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

}
