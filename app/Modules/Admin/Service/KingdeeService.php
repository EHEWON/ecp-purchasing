<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Common\Models\Kingdee\{
    Notice,
    Announcement,
    BidProject
};
use App\Modules\Admin\Repository\KingdeeRepo;
use Illuminate\Http\Request;

class KingdeeService extends Service {

    public function getRules() {
        return [];
    }

    public $middleware = [];

    public function noticeList(Request $request) {
        $qurey = Notice::selectRaw('notice_id as id,title,publish_date,biztype,status,org_id,org_name');

        $qurey->whereIn('biztype', ['1', '2', '5', 'A']);

        if (!empty($request->keyword)) {
            $qurey->where('title', 'like', '%' . trim($request->keyword) . '%');
        }
        if (!empty($request->publish_date)) {
            $qurey->whereBetween('publish_date', explode(',', $request->publish_date));
        }
        if (!empty($request->status)) {
            $qurey->where('status', trim($request->status));
        }
        if (!empty($request['biztype'])) {
            $qurey->whereIn('biztype', explode(',', urldecode(trim($request['biztype']))));
        }
        $qurey->orderBy('publish_date', 'desc');

        $condition = $request->all();
        $pageSize = 10;
        if (isset($condition['pagesize'])) {
            $pageSize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        } elseif (isset($condition['limit'])) {
            $pageSize = intval($condition['limit']) > 0 ? intval($condition['limit']) : 10;
        }
        $clone = $qurey->clone();
        $page = !empty($request->page) && (int) $request->page > 0 ? ((int) $request->page - 1) * $pageSize : 0;
        $object = $qurey->offset($page)->limit($pageSize)->get();
        $total = $clone->count();
        $list = [];
        if (empty($object)) {
            return [];
        }
        $list['total'] = $total;
        $list['data'] = $object->toArray();
        return $list;
    }

    public function noticeDetail(string $id) {
        $repo = new KingdeeRepo();
        return $repo->noticeDetail($id);
    }

    public function announcementList(Request $request) {
        $qurey = Announcement::selectRaw('announcement_id as id,title,publish_date,annotype,status,org_id,org_name');
        $qurey->whereIn('annotype', ['bidproject', 'decision']);
        if (!empty($request->keyword)) {
            $qurey->where('title', 'like', '%' . trim($request->keyword) . '%');
        }

        if (!empty($request->publish_date)) {
            $qurey->whereBetween('publish_date', explode(',', $request->publish_date));
        }
        if (!empty($request->status)) {
            $qurey->where('status', trim($request->status));
        }
        $clone = $qurey->clone();
        $qurey->orderBy('publish_date', 'desc');
        $pageSize = (int) $request->pagesize > 1 ? $request->pagesize : 10;
        $page = !empty($request->page) && (int) $request->page > 0 ? ((int) $request->page - 1) * $pageSize : 0;
        $object = $qurey->offset($page)->limit($pageSize)->get();
        $total = $clone->count();
        $list = [];
        if (empty($object)) {
            return [];
        }
        $list['total'] = $total;
        $list['data'] = $object->toArray();
        return $list;
    }

    public function announcementDetail(string $id) {
        $notice = Announcement::where('announcement_id', $id)->first();
        if (empty($notice)) {
            return [];
        }
        return $notice->toArray();
    }

    public function bidDetail(string $id) {
        $notice = BidProject::where('id', $id)->first();
        if (empty($notice)) {
            return [];
        }
        $announcementDetail = Announcement::where('bidproject', $notice->project_id)->first();
        if (!empty($announcementDetail)) {
            $notice['currentstep'] = ',BidAnnouncement,';
        }
        if ($notice->bidopentype == 'UNIONOPEN') {
            $notice->bidopentype = '统一开标';
        } else if ($notice->bidopentype == 'TECHBUSINESS') {
            $notice->bidopentype = '先技术后商务';
        }
        return $notice->toArray();
    }

    public function groupList(Request $request) {
        $repo = new KingdeeRepo();
        $data = [];
        $request->merge(['pagesize' => 5]);
        $data['inquiry'] = $repo->getListByType('inquiry', ['pagesize' => 5]);
        $data['bidding'] = $repo->getListByType('bidding', ['pagesize' => 5]);
        $data['auction'] = $repo->getListByType('auction', ['pagesize' => 5]);
        return $data;
    }

    public function search(Request $request) {
        $repo = new KingdeeRepo();
        $data = [];
        $data['list'] = $repo->getList($request->all());
        return $data;
    }

    public function getMessages() {
        
    }

}
