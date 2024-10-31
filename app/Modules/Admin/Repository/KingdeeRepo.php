<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Kingdee\Announcement,
    Kingdee\BidProject,
    Kingdee\Invitation,
    Kingdee\Login,
    Kingdee\Supplier,
    Notice,
    NoticeSub,
    Purchaser
};
use Illuminate\Support\Facades\{
    Cache,
    DB
};
use Illuminate\Http\Request;

class KingdeeRepo extends Repository {

    const GET_APP_TOKEN = '/api/getAppToken.do'; //获取apptoken
    const GET_ACCESS_TOKEN = '/api/login.do'; //获取access_token
    const ACCOUNT_ID = '1180962029842726912';
    const TENANT = 'erui';
    const TAG = 'ECP';

//金蝶获取apptoken和登录token参数

    private $kAppId = 'wechat';
    private $kAppSecuret = '12345678987654321';
    private $tenantid = 'erui';
    private $language = 'zh_CN';
    //金蝶获取登录token参数
    private $user = 'webchat@tempmail.cn';
    private $userType = 'Email';
    protected $accessToken = '';

    const INQUIRY_DETAIL = '/kapi/sys/sou_notice/load'; //询竞价消息详情
    const BID_DETAIL = '/kapi/sys/bid_announcement/load'; //招投标消息详情
    const KAPI_HOST = 'https://ecp.erui.com';

    protected $model;

    public function __construct() {
        $model = new Notice ();
        parent::__construct($model);
    }

    public function getCreatedCount(array $request) {

        if (!empty($request['created_type'])) {
            unset($request['created_type']);
        }
        $today = date('Y-m-d');
        $last3Days = date('Y-m-d', strtotime('-2 days'));
        $last7Days = date('Y-m-d', strtotime('-6 days'));
        $last30Days = date('Y-m-d', strtotime('-29 days'));
        $last90Days = date('Y-m-d', strtotime('-89 days'));
        $last180Days = date('Y-m-d', strtotime('-179 days'));
        $nmodel = new Notice();
        $amodel = new Announcement();
        $nqurey = $nmodel
                ->selectRaw('notice_id as id,title,'
                . 'publish_date,org_name,(case biztype WHEN \'5\' THEN \'bidding\'ELSE \'inquiry\' END ) AS type,org_id,biztype');
        $aqurey = $amodel
                ->selectRaw('announcement_id as id,title,'
                . 'publish_date,org_name,\'bidding\' AS type,org_id,\'2\' as biztype');
        $nqurey->unionAll($aqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        $this->getWhere($qurey, $request);
        $object = $qurey
                ->selectRaw('sum(if(publish_date>\'' . $today . '\',1,0)) AS today ,'
                        . 'sum(if(publish_date>\'' . $last3Days . '\',1,0)) AS last_3_days ,'
                        . 'sum(if(publish_date>\'' . $last7Days . '\',1,0)) AS last_7_days ,'
                        . 'sum(if(publish_date>\'' . $last30Days . '\',1,0)) AS last_30_days ,'
                        . 'sum(if(publish_date>\'' . $last90Days . '\',1,0)) AS last_90_days ,'
                        . 'sum(if(publish_date>\'' . $last180Days . '\',1,0)) AS last_180_days '
                )
                ->first();
        if (empty($object)) {
            return [];
        }
        return $object;
    }

    public function getTypeCount(array $request) {
        if (!empty($request['types'])) {
            unset($request['types']);
        }
        $nmodel = new Notice();
        $amodel = new Announcement();
        $nqurey = $nmodel
                ->selectRaw('notice_id as id,title,'
                . 'publish_date,org_name,\'inquiry\' AS type,org_id,biztype');
        $aqurey = $amodel
                ->selectRaw('announcement_id as id,title,'
                . 'publish_date,org_name,\'bidding\' AS type,org_id,\'2\' as biztype');
        $nqurey->unionAll($aqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        $this->getWhere($qurey, $request);
        $qurey->selectRaw('type,count(id) as num');
        $object = $qurey->groupBy('c.type')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        return $list;
    }

    public function getBizTypeCount(array $request) {
        if (!empty($request['biztypes'])) {
            unset($request['biztypes']);
        }
        $nmodel = new Notice();
        $amodel = new Announcement();
        $nqurey = $nmodel
                ->selectRaw('notice_id as id,title,'
                . 'publish_date,org_name,\'inquiry\' AS type,org_id,biztype');
        $aqurey = $amodel
                ->selectRaw('announcement_id as id,title,'
                . 'publish_date,org_name,\'bidding\' AS type,org_id,\'2\' as biztype');
        $nqurey->unionAll($aqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        $this->getWhere($qurey, $request);
        $qurey->selectRaw('biztype,count(id) as num');
        $object = $qurey->groupBy('c.biztype')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        return $list;
    }

    public function getOrgCount(array $request) {
        if (!empty($request['orgs'])) {
            unset($request['orgs']);
        }
        $nmodel = new Notice();
        $amodel = new Announcement();
        $nqurey = $nmodel
                ->selectRaw('notice_id as id,title,'
                . 'publish_date,org_name,\'inquiry\' AS type,org_id,biztype');
        $aqurey = $amodel
                ->selectRaw('announcement_id as id,title,'
                . 'publish_date,org_name,\'bidding\' AS type,org_id,\'2\' as biztype');
        $nqurey->unionAll($aqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        $this->getWhere($qurey, $request);
        $qurey->selectRaw('org_name,org_id,count(id) as num');
        $object = $qurey->groupBy('c.org_id')->groupBy('c.org_name')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        return $list;
    }

    public function getList(array $request) {
        $nmodel = new Notice();
        $bmodel = new BidProject();
        $cmodel = new Invitation();
        $nqurey = $nmodel
                ->selectRaw('notice_id as id,title,'
                        . 'publish_date,org_name,\'inquiry\' AS type,org_id,biztype,duedate,status')
                ->whereRaw('biztype IN (1,\'a\',3,\'b\')');
        $bqurey = $bmodel
                ->selectRaw('id,name as title,'
                        . 'createtime as publish_date,org_name,\'bid_project\' AS type,\'\' as org_id,\'5\' as biztype,enrolldeadline as duedate,currentstep as status')
                ->whereRaw('currentstep like \'%BidDecision%\'');
        $cquery = $cmodel
                ->from('kingdee_invitation as ki')
                ->selectRaw('oc_bp.id,oc_ki.name as title,oc_ki.publish_date,oc_ki.org_name,\'bid_project\' AS type,oc_ki.org_id,\'2\' as biztype,oc_ki.deadlinedate as duedate,oc_bp.currentstep as status')
                ->join('kingdee_bid_project as bp', 'bp.project_id', '=', 'ki.bidproject_id');
        // $nqurey->unionAll($aqurey);
        $nqurey->unionAll($bqurey);
        $nqurey->unionAll($cquery);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        $this->getWhere($qurey, $request);
        $clone = $qurey->clone();
        $qurey->orderBy('publish_date', 'desc');
        $nrequest = new Request();
        $nrequest->merge($request);
        $this->getPage($qurey, $nrequest);
        $object = $qurey->get();
        $total = $clone->count();
        $list = [];
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();

        foreach ($data as &$item) {
            if ($item['biztype'] == 1) {
                $item['type_name'] = '询价';
            } else if ($item['biztype'] == 'A') {
                $item['type_name'] = '询价';
            } else if ($item['biztype'] == 2) {
                $item['type_name'] = '招标';
            } else if ($item['biztype'] == 3) {
                $item['type_name'] = '竞标';
            } else if ($item['biztype'] == 'B') {
                $item['type_name'] = '竞标';
            } else if ($item['biztype'] == 5) {
                $item['type_name'] = '定标';
            } else {
                $item['type_name'] = '其他';
            }
            $item['status_name'] = $this->getStatusName($item['biztype'], $item['status']);
            $item['publish_date'] = date('Y-m-d', strtotime($item['publish_date']));
            $item['left_time'] = leftTimeDisplay($item['duedate']);
        }
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getListByType($type, array $request) {
        switch ($type) {
            case 'inquiry': {
                    // 询价信息
                    $bizType = 1;
                    $table = 'notice';
                    break;
                }
            case 'inquiry_result': {
                    // 询价结果公告
                    $bizType = 'a';
                    $table = 'notice';
                    break;
                }
            case 'bidding': {
                    // 招标公示
                    $bizType = '2';
                    $table = 'notice';
                    break;
                }
            case 'bidding_result': {
                    // 招标公告
                    $bizType = '5';
                    $table = 'notice';
                    break;
                }
            case 'auction': {
                    // 竞价公告 
                    $bizType = 3;
                    $table = 'notice';
                    break;
                }
            case 'auction_result': {
                    // 竞价结果 
                    $bizType = 'b';
                    $table = 'notice';
                    break;
                }
        }


        $notice = (new Notice())->getTable();
        $noticeSub = (new NoticeSub())->getTable();
        $org = (new Purchaser())->getTable();
        $query = Notice::from($notice . ' AS i')
                ->join($noticeSub . ' AS ns', function($join) {
                    $join->on('i.id', '=', 'ns.notice_id');
                })
                ->join($org . ' AS o', function($join) {
                    $join->on('i.org_id', '=', 'o.id');
                })
                ->selectRaw('i.id,i.title,i.bill_date AS publish_date,i.org_id,o.name AS org_name'
                        . ',i.biz_type AS biztype,i.due_date AS duedate')
                ->where('i.biz_type', $bizType)
                ->whereRaw('i.sup_scope=\'1\'')
                ->where('i.bill_status', 'C');
        $nrequest = new Request();
        $nrequest->merge($request);
        $this->getPage($query, $nrequest);
        $query->orderBy('publish_date', 'desc');
        $data = $query->get()->toArray();
        foreach ($data as &$item) {
            $item['left_time'] = leftTimeDisplay($item['duedate']);
        }
        return $data;
    }

    public function getCount(array $request) {
        $nmodel = new Notice();
        $amodel = new Announcement();
        $nqurey = $nmodel
                ->selectRaw('notice_id as id,title,'
                . 'publish_date,org_name,\'inquiry\' AS type,org_id,biztype');
        $aqurey = $amodel
                ->selectRaw('announcement_id as id,title,'
                . 'publish_date,org_name,\'bidding\' AS type,org_id,\'2\' as biztype');
        $nqurey->unionAll($aqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        $this->getWhere($qurey, $request);
        return $qurey->count();
    }

    protected function getWhere(&$query, array $request) {
        // $query->whereIn('c.biztype', ['1', '2', '5', 'a']);
        if (!empty($request['keyword'])) {
            $keyword = urldecode(trim($request['keyword']));
            $query->where('c.title', 'like', '%' . $keyword . '%');
        }

        if (!empty($request['orgs'])) {
            $query->whereIn('c.org_id', explode(',', urldecode(trim($request['orgs']))));
        }
        if (!empty($request['org_name'])) {
            $query->where('c.org_name', 'like', '%' . $request['org_name'] . '%');
        }
        if (!empty($request['biztypes'])) {
            $query->whereIn('c.biztype', explode(',', urldecode(trim($request['biztypes']))));
        }
        if (isset($request['created']) && is_numeric($request['created'])) {
            $createdAt = date('Y-m-d', strtotime('-' . intval($request['created']) . ' days'));
            $query->where('c.publish_date', '>=', trim($createdAt));
        }
        if (isset($request['published_ats']) && is_array($request['published_ats'])) {
            $query->whereBetween('c.publish_date', $request['published_ats']);
        }
        if (isset($request['expired'])) {
            if ($request['expired'] == 'Y') {
                $query->where('c.duedate', '<', date('Y-m-d'));
            } else if ($request['expired'] == 'N') {
                $query->where('c.duedate', '>=', date('Y-m-d'));
            }
        }
        if (!empty($request['types'])) {
            $query->whereIn('c.type', explode(',', urldecode(trim($request['types']))));
        }
    }

    public function getMembersCount() {
        $lmodel = new Login();
        $smodel = new Supplier();
        $nqurey = $lmodel->selectRaw('user_id as id');
        $aqurey = $smodel->selectRaw('supplier_id as id');
        $nqurey->unionAll($aqurey);
        $sql = $nqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));

        return $qurey->selectRaw('count(DISTINCT id) as num')->value('num');
    }

    public function getBusinessCount() {
        $imodel = new \App\Common\Models\KingdeeInquiry();
        $dmodel = new \App\Common\Models\KingdeeDecision();
        $bmodel = new \App\Common\Models\KingdeeBid();
        $iqurey = $imodel->selectRaw('inquiry_id as id')->groupBy('inquiry_id');
        $dqurey = $dmodel->selectRaw('decision_id as id')->groupBy('decision_id');
        $bqurey = $bmodel->selectRaw('bid_id as id')->groupBy('bid_id');
        $iqurey->unionAll($dqurey);
        $iqurey->unionAll($bqurey);
        $sql = $iqurey->toSql();
        $qurey = DB::connection('xd_db')->table(DB::raw('(' . $sql . ')  oc_c'));
        return $qurey->count();
    }

    public function noticeDetail($id) {
        $notice = Notice::where('notice_id', $id)->first();
        if (empty($notice)) {
            return $this->inquiryInfo($id, 'INSERT');
        }
        $data = $notice->toArray();
        if (empty($data['content'])) {
            return $this->inquiryInfo($id, 'UPDATE');
        }
        $data['left_time'] = leftTimeDisplay($data['duedate'] ?? 0);
        return $data;
    }

    /**
     * @desc  询竞价标详情
     */
    public function inquiryInfo($id, $type = 'UPDATE') {
        $this->getAccessToken();
        $action = self::INQUIRY_DETAIL . '?id=' . $id;
        $detail = $this->kingdeePost([], $action, $timeout = 30, []);
        if (empty($detail) || $detail['success'] === false || $detail['success'] === 'false') {
            Notice::where('notice_id', $id)->delete();
            check(false, '该数据在系统中不存在，可能已经被删除。');
        }
        $ret = [];
        if (!empty($detail)) {
            $item = $detail['data'];
            $ret = [
                'notice_id' => $item['id'],
                'title' => $item['noticetitle']['zh_CN'],
                'org_id' => $item['org']['id'],
                'org_name' => $item['org']['name']['zh_CN'],
                'publish_date' => $item['billdate'],
                'billno' => $item['billno'],
                'srcbillno' => $item['srcbillno'],
                'biztype' => $item['biztype'],
                'status' => $item['billstatus'],
                'content' => $item['content'],
            ];
        }
        if ($type === 'UPDATE') {
            Notice::where('notice_id', $id)->update(['content' => $ret['content']]);
        } elseif ($type === 'INSERT') {
            Notice::insertGetId($ret);
        }
        return $ret;
    }

    public function announcementDetail($id) {
        $notice = Announcement::where('announcement_id', $id)->first();
        if (empty($notice)) {
            return $this->announcementInfo($id, 'INSERT');
        }
        $data = $notice->toArray();
        if (empty($data['content'])) {
            return $this->announcementInfo($id, 'UPDATE');
        }
        $data['left_time'] = leftTimeDisplay($item['signendtime'] ?? 0);
        return $data;
    }

    public function announcementInfo($id, $type = 'UPDATE') {
        $this->getAccessToken();
        $action = self::BID_DETAIL . '?id=' . $id;
        $detail = $this->kingdeePost([], $action, $timeout = 30, []);
        if (empty($detail) || $detail['success'] === false || $detail['success'] === 'false') {
            Announcement::where('announcement_id', $id)->delete();
            check(false, '该数据在系统中不存在，可能已经被删除。');
        }
        $ret = [];
        if (!empty($detail)) {
            $item = $detail['data'];
            $ret = [
                'announcement_id' => $item['id'],
                'title' => $item['annotitle']['zh_CN'],
                'org_id' => $item['org']['id'],
                'org_name' => $item['org']['name']['zh_CN'],
                'publish_date' => $item['publishdate'],
                'billno' => $item['billno'],
                'annotype' => $item['annotype'],
                'status' => $item['billstatus'],
                'content' => $item['content'],
            ];
        }
        if ($type === 'UPDATE') {
            Announcement::where('announcement_id', $id)->update(['content' => $ret['content']]);
        } elseif ($type === 'INSERT') {
            Announcement::insertGetId($ret);
        }
        return $ret;
    }

    public function lastSupplier() {
        $model = new Supplier();
        return $model->select('name as createorg_name', 'createtime')->orderBy('id', 'desc')->limit(10)->get()->toArray();
    }

    public function lastInquiry() {
        $model = new KingdeeInquiry();
        return $model->select('inquirytitle', 'publish_date')->orderBy('id', 'desc')->limit(10)->get()->toArray();
    }

    private function getStatusName($bizType, $status) {
        if ($bizType == 5) {
            if (strstr($status, 'BidProject') != false) {
                return '招标公告';
            } else if (strstr($status, 'BidPublish') != false) {
                return '招标文件发布';
            } else if (strstr($status, 'BidAnswerQuestion') != false) {
                return '答疑/澄清';
            } else if (strstr($status, 'BidEvaluation') != false) {
                return '在线评标';
            } else if (strstr($status, 'BidDecision') != false) {
                return '定标';
            } else if (strstr($status, 'BidAnnouncement') != false) {
                return '中标通知书';
            }
        } else {
            switch (strtoupper($status)) {
                case 'A': return '采购公告';
                case 'B': return '报价阶段';
                case 'C': return '开标阶段';
                case 'D': return '比价阶段';
                case 'E': return '结果公示';
            }
        }
    }

//获取金蝶access_token
    private function getAccessToken() {
        if (!empty($this->accessToken)) {
            return $this->accessToken;
        }
        $redisKey = 'kingdee:' . self::GET_ACCESS_TOKEN;
        $this->accessToken = Cache::remember($redisKey, 7000, function () {
                    $appToken = $this->getAppToken();
                    if (!$appToken) {
                        return false;
                    }
                    $data = [
                        'user' => $this->user,
                        'apptoken' => $appToken,
                        'tenantid' => $this->tenantid,
                        'accountId' => self::ACCOUNT_ID,
                        'usertype' => $this->userType,
                        'language' => $this->language
                    ];
                    $action = self::GET_ACCESS_TOKEN;
                    $result = $this->kingdeePost($data, $action, $timeout = 30, []);
                    if (!empty($result['data']['access_token'])) {
                        $this->accessToken = $result['data']['access_token'];
                        return $result['data']['access_token'];
                    }
                    return false;
                });
    }

    private function getAppToken() {
        $data = [
            'appId' => $this->kAppId,
            'appSecuret' => $this->kAppSecuret,
            'tenantid' => $this->tenantid,
            'accountId' => self::ACCOUNT_ID,
            'language' => $this->language
        ];
        $action = self::GET_APP_TOKEN;
        $result = $this->kingdeePost($data, $action, $timeout = 30, []);

        if (!empty($result['data']['app_token'])) {
            return $result['data']['app_token'];
        }
        return false;
    }

    function kingdeePost($data, $action, $timeout = 30, $headers = []) {
        $header = ['Content-type: application/json'];

        if ($headers) {
            foreach ($headers as $key => $val) {
                $header[] = $key . ':' . $val;
            }
        }
        if ($this->accessToken) {
            $header[] = 'accessToken:' . $this->accessToken;
        }
        $formdata = json_encode($data);
        $url = self::KAPI_HOST . $action;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {

            return [];
        }
        curl_close($ch);
        return json_decode($response, true);
    }

}
