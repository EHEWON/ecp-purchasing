<?php

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Notice,
    BidBill\BidBill,
    NoticeSub
};
use \App\Modules\Admin\Repository\{
    UserRepo,
    OrgRepo
};
use Illuminate\Http\Request;

class BiddingRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBill();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $qurey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'inquiry\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type IN (3,\'b\')');
        $this->getWhere($qurey, $request->all());
        $clone = $qurey->clone();
        $qurey->orderBy('publish_date', 'desc');
        $this->getPage($qurey, $request);
        $object = $qurey->get();
        $total = $clone->count();
        $list = [];
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['id'] = (string) $item['id'];
            if ($item['biztype'] == '3') {
                $item['type'] = 'bid_bill';
                $item['type_name'] = '竞标';
            } else if ($item['biztype'] == 'B') {
                $item['type'] = 'bid_bill';
                $item['type_name'] = '竞标';
            }
            $item['status_name'] = $this->getStatusName($item['status']);
            $item['publish_date'] = date('Y-m-d', strtotime($item['publish_date']));
            $item['left_time'] = leftTimeDisplay($item['duedate']);
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function noticeinfo($id) {
        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $obj = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.bill_no,i.title,i.bill_date,i.org_id'
                        . ',i.biz_type,i.due_date,ns.src_bill_no,i.bill_status,i.content,'
                        . 'ns.src_bill_id')
                ->where('i.id', $id)
                ->where('i.bill_status', 'C')
                ->first();
        if (empty($obj)) {
            return [];
        }
        $detail = $obj->toArray();
        if (empty($detail)) {
            return [];
        }
        (new OrgRepo)->setOrg($detail, 'org_id', 'org_name');
        $ret = [
            'due_date' => $detail['due_date'],
            'notice_id' => $detail['id'],
            'title' => $detail['title'],
            'org_id' => $detail['org_id'],
            'org_name' => $detail['org_name'],
            'publish_date' => $detail['bill_date'],
            'billno' => $detail['bill_no'],
            'srcbillno' => $detail['src_bill_no'],
            'src_bill_id' => (string) $detail['src_bill_id'],
            'biztype' => $detail['biz_type'],
            'status' => null,
            'content' => $detail['content'],
        ];

        if (empty($detail['biz_type'])) {
            return $ret;
        }
        switch ($detail['biz_type']) {
            case '3':
                $ret['entry'] = (new BiddingEntryRepo)->getList($detail['src_bill_id']);
                $ret['bid_bill'] = $this->info($detail['src_bill_id']);
                $ret['left_time'] = leftTimeDisplay($ret['bid_bill']['enroll_date'] ?? 0);
                $ret['status'] = !empty($ret['inquiry']['biz_status']) ? $ret['inquiry']['biz_status'] : null;
                break;
            case 'B':
                $ret['entry'] = (new BiddingEntryRepo)->getList($detail['src_bill_id']);
                $ret['bid_bill'] = $this->info($detail['src_bill_id']);
                $ret['left_time'] = leftTimeDisplay($ret['bid_bill']['enroll_date'] ?? 0);
                $ret['status'] = !empty($ret['inquiry']['biz_status']) ? $ret['inquiry']['biz_status'] : null;
                break;
        }

        return $ret;
    }

    protected function getWhere(&$query, array $request) {
        if (!empty($request['keyword'])) {
            $keyword = urldecode(trim($request['keyword']));
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        if (!empty($request['orgs'])) {
            $query->whereIn('org_id', explode(',', urldecode(trim($request['orgs']))));
        }
        if (!empty($request['org_name'])) {
            $query->where('org_name', 'like', '%' . $request['org_name'] . '%');
        }
        if (!empty($request['biztypes'])) {
            $query->whereIn('biztype', explode(',', urldecode(trim($request['biztypes']))));
        }
        if (isset($request['created']) && is_numeric($request['created'])) {
            $createdAt = date('Y-m-d', strtotime('-' . intval($request['created']) . ' days'));
            $query->where('publish_date', '>=', trim($createdAt));
        }
        if (isset($request['published_ats']) && is_array($request['published_ats'])) {
            $query->whereBetween('publish_date', $request['published_ats']);
        }
        if (isset($request['expired'])) {
            if ($request['expired'] == 'Y') {
                $query->where('duedate', '<', date('Y-m-d'));
            } else if ($request['expired'] == 'N') {
                $query->where('duedate', '>=', date('Y-m-d'));
            }
        }
        if (!empty($request['types'])) {
            $query->whereIn('biztype', explode(',', urldecode(trim($request['types']))));
        }
    }

    /**
     * 获取合同列表
     * @return array
     */
    public function info($id) {
        $query = $this->model->selectRaw('*');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $base = $object->toArray();
        (new UserRepo())->setUser($base, 'person_id', 'person_name');
        $base['inv_type_name'] = $this->getInvtypeText($base['inv_type']);
        return $base;
    }

    private function getStatusName($status) {
        switch (strtoupper($status)) {
            case 'A': return '采购公告';
            case 'B': return '报价阶段';
            case 'C': return '开标阶段';
            case 'D': return '比价阶段';
            case 'E': return '结果公示';
        }
    }

    public function getInvtypeText($invtype) {
        switch (strtoupper($invtype)) {
            case '1':
                return '普通电子发票';
            case '2':
                return '电子发票专票';
            case '3':
                return '普通纸质发票';
            case '4':
                return '专用纸质发票';
            case '5':
                return '普通纸质卷票';
            case '6':
                return '增值税专用发票';
            case '7':
                return '增值税普通发票';
            case '9':
                return '不需要发票';
        }
    }

}
