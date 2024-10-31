<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\NoticeAttach;
use Illuminate\Http\Request;

class NoticeAttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new NoticeAttach();
        parent::__construct($this->model);
    }

    public function getList(int $noticeId) {
        if (empty($noticeId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('notice_id', $noticeId);
        $qurey->where('deleted_flag', 'N');
        return $qurey->orderBy('id', 'ASC')->get();
    }

    public function updateData(int $noticeId, Request $request) {
        NoticeAttach::where('notice_id', $noticeId)->delete();
        $attachList = $this->getAttachs($noticeId, $request);
        if (!empty($attachList)) {
            NoticeAttach::insert($attachList);
        }
    }

    public function getAttachs(int $noticeId, Request $request) {
        $attachList = [];
        if (empty($request->attach)) {
            return [];
        }
        foreach ($request->attach as $attach) {
            if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                continue;
            }
            $attachList[] = [
                'notice_id' => $noticeId,
                'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        return $attachList;
    }

}
