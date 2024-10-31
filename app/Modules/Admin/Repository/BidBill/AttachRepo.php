<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\Attach;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Attach();
        parent::__construct($this->model);
    }

    public function getList(int $bidBillId) {
        if (empty($bidBillId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('bid_bill_id', $bidBillId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        return $list;
    }

    public function updateData(int $bidBillId, Request $request) {
        Attach::where('bid_bill_id', $bidBillId)->delete();
        $attachList = $this->getAttachs($bidBillId, $request);
        if (!empty($attachList)) {
            Attach::insert($attachList);
        }
    }

    public function getAttachs(int $bidBillId, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'bid_bill_id' => $bidBillId,
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
