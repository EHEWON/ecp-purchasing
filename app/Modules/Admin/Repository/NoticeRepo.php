<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    BidBill\BidBill,
    Inquiry\Inquiry,
    Project\Project,
    Notice,
    NoticeSub,
    NoticeUser
};
use App\Modules\Admin\Repository\{
    Inquiry\InquiryRepo,
    UserRepo,
    OrgRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Redis
};

class NoticeRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'title',
        'org_id',
        'bill_date',
        'is_top',
        'due_date',
        'src_bill_no',
        'sup_scope',
        'biz_type',
    ];

    public function __construct() {
        $this->model = new Notice();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $query->orderBy('n.is_top', 'DESC');
        $query->orderBy('nu.read_flag', 'ASC');
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'bill_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'bill_date') {
            $query->orderBy('bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $token = Auth::guard('admin')->getToken();
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $redisKey = md5($token);
        $curId = 0;
        if (!empty($token) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $noticeTable = $this->model->getTable();
        $noticesubTable = (new NoticeSub)->getTable();
        $noticeUserTable = (new NoticeUser)->getTable();
        $inquiryTable = (new Inquiry)->getTable();
        $bidBillTable = (new BidBill)->getTable();
        $projectTable = (new Project)->getTable();
        $query = $this->model
                ->selectRaw('n.id,n.bill_no,n.bill_date,n.due_date,'
                        . 'n.biz_type,n.sup_scope,n.bill_status,n.org_id,n.title,'
                        . 'ns.src_bill_id,ns.src_bill_no,ns.src_bill_type,n.is_top')
                ->from($noticeTable . ' as n')
                ->join($noticesubTable . ' as ns', function ($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->leftJoin($noticeUserTable . ' as nu', function ($join)use($userId) {
                    $join->on('n.id', '=', 'nu.notice_id')
                    ->where('nu.user_id', $userId);
                })
                ->leftJoin($inquiryTable . ' as i', function ($join) {
                    $join->on('i.id', '=', 'ns.src_bill_id')
                    ->whereIn('ns.src_bill_type', ['sou_compare', 'sou_inquiry'])
                    ->where('i.deleted_flag', 'N');
                })
                ->whereIn('ns.src_bill_type', ['sou_compare', 'sou_inquiry', 'sou_bidbill', 'sou_bidbillcfm', 'sou_decision', 'sou_project'])
                ->leftJoin($bidBillTable . ' as b', function ($join) {
                    $join->on('b.id', '=', 'ns.src_bill_id')
                    ->whereIn('ns.src_bill_type', ['sou_bidbill', 'sou_bidbillcfm']);
                })
                ->leftJoin($projectTable . ' as p', function ($join) {
            $join->on('p.id', '=', 'ns.src_bill_id')
            ->whereIn('ns.src_bill_type', ['sou_decision', 'sou_project']);
        });

        $query->where(function($qs)use($userId) {
            $qs->where('i.person_id', $userId)
                    ->orWhere('i.created_by', $userId)
                    ->orWhere('b.person_id', $userId)
                    ->orWhere('b.created_by', $userId)
                    ->orWhere('p.created_by', $userId)
                    ->orWhere('p.contact_id', $userId);
        });
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
        $inquiryRepo = new InquiryRepo();
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        foreach ($data as &$item) {
            $item['src_bill_id'] = (string) $item['src_bill_id'];
            $item['biz_type_name'] = $this->getBizTypeText($item['biz_type']);
            $item['bill_status_name'] = $this->getStatusText($item['bill_status']);
            $item['src_bill_type_name'] = $this->getSrcBillTypeText($item['src_bill_type']);
            $item['sup_scope_name'] = $inquiryRepo->getSupScopeText($item['sup_scope']);
        }
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
    public function noticeInfo($projectId) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $noticeTable = $this->model->getTable();
        $subTable = (new NoticeSub)->getTable();
        $query = $this->model
                ->selectRaw('*')
                ->from($noticeTable . ' as n')
                ->join($subTable . ' as ns', function($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->where('biz_type', '2')
                ->where('ns.src_bill_id', $projectId);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $id = $data['id'];
        $inquiryRepo = new InquiryRepo();
        (new OrgRepo)->setOrg($data, 'org_id', 'org_name');
        $data['src_bill_id'] = (string) $data['src_bill_id'];
        $data['notice_id'] = (string) $data['notice_id'];
        $data['modifier_id'] = (string) $data['modifier_id'];
        $data['auditor_id'] = (string) $data['auditor_id'];
        $data['creator_id'] = (string) $data['creator_id'];
        $data['biz_type_name'] = $this->getBizTypeText($data['biz_type']);
        $data['bill_status_name'] = $this->getStatusText($data['bill_status']);
        $data['sup_scope_name'] = (new InquiryRepo)->getSupScopeText($data['src_bill_type']);
        $data['src_bill_type_name'] = $this->getSrcBillTypeText($data['src_bill_type']);
        $data['sup_scope_name'] = $inquiryRepo->getSupScopeText($data['sup_scope']);
        (new UserRepo)->setUser($data, 'creator_id', 'creator_name');
        $data['attach'] = (new NoticeAttachRepo)->getList($id);
        $nuser = NoticeUser::where('notice_id', $id)
                ->where('user_id', $userId)
                ->count();
        if (empty($nuser)) {
            NoticeUser::insert([
                'notice_id' => $id,
                'user_id' => $userId,
                'read_flag' => 'Y',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $data;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $noticeTable = $this->model->getTable();
        $subTable = (new NoticeSub)->getTable();
        $query = $this->model
                ->selectRaw('*')
                ->from($noticeTable . ' as n')
                ->join($subTable . ' as ns', function($join) {
                    $join->on('n.id', '=', 'ns.notice_id');
                })
                ->where('n.id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new OrgRepo)->setOrg($data, 'org_id', 'org_name');
        $inquiryRepo = new InquiryRepo();
        $data['src_bill_id'] = (string) $data['src_bill_id'];
        $data['notice_id'] = (string) $data['notice_id'];
        $data['modifier_id'] = (string) $data['modifier_id'];
        $data['auditor_id'] = (string) $data['auditor_id'];
        $data['creator_id'] = (string) $data['creator_id'];
        $data['biz_type_name'] = $this->getBizTypeText($data['biz_type']);
        $data['bill_status_name'] = $this->getStatusText($data['bill_status']);
        $data['sup_scope_name'] = (new InquiryRepo)->getSupScopeText($data['src_bill_type']);
        $data['src_bill_type_name'] = $this->getSrcBillTypeText($data['src_bill_type']);
        $data['sup_scope_name'] = $inquiryRepo->getSupScopeText($data['sup_scope']);
        (new UserRepo)->setUser($data, 'creator_id', 'creator_name');
        $data['attach'] = (new NoticeAttachRepo)->getList($id);
        $nuser = NoticeUser::where('notice_id', $id)
                ->where('user_id', $userId)
                ->count();
        if (empty($nuser)) {
            NoticeUser::insert([
                'notice_id' => $id,
                'user_id' => $userId,
                'read_flag' => 'Y',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $data;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function read(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return MessageReceiver::whereIn('message_id', $ids)
                        ->where('receiver_id', $admin->user_id)
                        ->update([
                            'read_flag' => 'Y',
                            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Notice::whereIn('id', $ids)->delete();
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
                $q->where('n.title', 'like', '%' . $keyword . '%')
                        ->orWhere('ns.src_bill_no', 'like', '%' . $keyword . '%')
                        ->orWhere('n.bill_no', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->bill_status)) {
            $status = $request->bill_status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('bill_status', $statusies);
        }
        if (!empty($request->biz_type)) {
            $bizType = $request->biz_type;
            $bizTypes = is_array($bizType) ? $bizType : explode(',', trim($bizType));
            $query->whereIn('biz_type', $bizTypes);
        }
        if (!empty($request->org_id)) {
            $orgId = $request->org_id;
            $orgIds = is_array($orgId) ? $orgId : explode(',', trim($orgId));
            $query->whereIn('org_id', $orgIds);
        }
        if (!empty($request->read_flag)) {
            $readFlag = trim($request->read_flag);
            $readFlag == 'N' ? $query->whereRaw('ISNULL(nu.read_flag)') : $query->whereRaw('nu.id IS NOT NULL');
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('n.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('n.bill_date', $createAts);
        }
    }

    public function getStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '保存';
            case 'B':
                return '已提交';
            case 'C':
                return '已审核';
        }
    }

    public function getStatusList() {
        return [
            'A' => '保存',
            'B' => '已提交',
            'C' => '已审核',
        ];
    }

    /**
     * 
     * 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告
     */
    public function getBizTypeText($bizType) {
        switch (strtoupper($bizType)) {
            case '1':
                return '询价公告';
            case '2':
                return '招标公告';
            case '3':
                return '竞价公告';
            case '4':
                return '比价公告';
            case '5':
                return '中标公告';
            case '6':
                return '招募公告';
            case '7':
                return '行业动态';
            case '8':
                return '系统公告';
            case 'A':
                return '询价结果公告';
            case 'B':
                return '竞价结果公告';
        }
    }

    /**
     * 
     * 1询价公告 2招标公告 3 竞价公告 4比价公告 5 中标公告  6招募公告 7 行业动态 8系统公告  A 询价结果公告  B竞价结果公告
     */
    public function getSrcBillTypeText($srcBillType) {
        switch (strtolower($srcBillType)) {
            case 'sou_inquiry':
                return '询价公告';
            case 'sou_compare':
                return '比价公告';
            case 'sou_bidbill':
                return '竞价公告';
            case 'sou_bidbillcfm':
                return '竞价结果公告';
            case 'sou_decision':
                return '招标结果公告';
            case 'sou_project':
                return '招标公告';
        }
    }

    public function getBizTypeList() {
        return ['1' => '询价公告',
            '2' => '招标公告',
            '3' => '竞价公告',
            '4' => '比价公告',
            '5' => '中标公告',
            '6' => '招募公告',
            '7' => '行业动态',
            '8' => '系统公告',
            'A' => '询价结果公告',
            'B' => '竞价结果公告',
        ];
    }

    public function getSrcBillTypeList() {
        return ['sou_inquiry' => '询价',
            'sou_compare' => '比价'
        ];
    }

}
