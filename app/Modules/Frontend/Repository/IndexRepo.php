<?php

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Notice,
    NoticeSub,
    Supplier
};
use App\Modules\Admin\Repository\OrgRepo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class IndexRepo extends Repository {

    protected $model;

    public function __construct() {
        $model = new Notice ();
        parent::__construct($model);
    }

    public function lastQuote() {
        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $nqurey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'inquiry\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type=\'a\'');
        $equrey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'bid_bill\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type=\'b\'');
        $bqurey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'tendering\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type=\'5\'');
        $nqurey->unionAll($equrey);
        $nqurey->unionAll($bqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::table(DB::raw('(' . $sql . ')  oc_c'));
        $qurey->orderBy('publish_date', 'desc');
        $qurey->offset(0)->limit(6);
        $object = $qurey->get();
        if (empty($object)) {
            return [];
        }
        return $object->toArray();
    }

    public function getList(Request $request) {
        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $nqurey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'inquiry\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type IN (\'1\',\'a\')');
        $equrey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'bid_bill\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type IN (\'3\',\'b\')');
        $bqurey = Notice::from($notice . ' AS i')
                ->leftJoin($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->selectRaw('i.id,i.title,i.bill_date as publish_date'
                        . ',\'tendering\' AS type,i.org_id,i.biz_type AS biztype,due_date AS duedate,'
                        . 'i.bill_status AS `status`')
                ->whereRaw('i.bill_status=\'C\'')
                ->whereRaw('i.sup_scope=\'1\'')
                ->whereRaw('i.biz_type IN (\'2\',\'5\')');
        $nqurey->unionAll($equrey);
        $nqurey->unionAll($bqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::table(DB::raw('(' . $sql . ')  oc_c'));
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
            if ($item['biztype'] == 1) {
                $item['type'] = 'inquiry';
                $item['type_name'] = '询价';
            } else if ($item['biztype'] == 'A') {
                $item['type'] = 'inquiry';
                $item['type_name'] = '询价';
            } else if ($item['biztype'] == '2') {
                $item['type'] = 'bid_project';
                $item['type_name'] = '招标';
            } else if ($item['biztype'] == '3') {
                $item['type'] = 'bid_bill';
                $item['type_name'] = '竞标';
            } else if ($item['biztype'] == 'B') {
                $item['type'] = 'bid_bill';
                $item['type_name'] = '竞标';
            } else if ($item['biztype'] == '5') {
                $item['type'] = 'bid_project';
                $item['type_name'] = '定标';
            } else {
                $item['type_name'] = '其他';
            }
            $item['status_name'] = $this->getStatusName($item['status']);
            $item['publish_date'] = date('Y-m-d', strtotime($item['publish_date']));
            $item['left_time'] = leftTimeDisplay($item['duedate']);
        }
        (new OrgRepo)->setOrgs($data, 'org_id', 'org_name');
        $list['total'] = $total;
        $list['data'] = $data;
        $list['request'] = $request->all();
        return $list;
    }

    public function lastSettled() {
        return Supplier::select('name', 'id')
                        ->where('source', 'REGISTER')
                        ->where('deleted_flag', 'N')
                        ->where('status', 'APPROVED')
                        ->orderBy('registered_at', 'desc')
                        ->offset(0)
                        ->limit(6)
                        ->get();
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

    protected function getWhere(&$query, array $request) {
        if (!empty($request['keyword'])) {
            $keyword = urldecode(trim($request['keyword']));
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        if (!empty($request['orgs'])) {
            $query->whereIn('i.org_id', explode(',', urldecode(trim($request['orgs']))));
        }

        if (!empty($request['biztypes'])) {
            $query->whereIn('biztype', explode(',', urldecode(trim($request['biztypes']))));
        }
         if (!empty($request['antypes'])) {
            $query->whereIn('biztype', explode(',', urldecode(trim($request['antypes']))));
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

}
