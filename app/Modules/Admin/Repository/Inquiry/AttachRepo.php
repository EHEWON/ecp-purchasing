<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use App\Common\Models\Inquiry\Attach;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Attach();
        parent::__construct($this->model);
    }

    public function getList(int $inquiryId) {
        if (empty($inquiryId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('inquiry_id', $inquiryId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
        }
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        return $list;
    }

    public function updateData(int $inquiryId, Request $request) {
        Attach::where('inquiry_id', $inquiryId)->delete();
        $attachList = $this->getAttachs($inquiryId, $request);
        if (!empty($attachList)) {
            Attach::insert($attachList);
        }
    }

    public function getAttachs(int $inquiryId, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'inquiry_id' => $inquiryId,
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $attachList;
    }

}
