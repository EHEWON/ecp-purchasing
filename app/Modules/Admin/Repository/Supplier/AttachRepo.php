<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\Quote\QuoteAttach;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new QuoteAttach();
        parent::__construct($this->model);
    }

    public function getList(int $quoteId) {
        if (empty($quoteId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('quote_id', $quoteId);
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

    public function updateData(int $quoteId, Request $request) {
        QuoteAttach::where('quote_id', $quoteId)->delete();
        $attachList = $this->getAttachs($quoteId, $request);
        if (!empty($attachList)) {
            QuoteAttach::insert($attachList);
        }
    }

    public function getAttachs(int $quoteId, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'quote_id' => $quoteId,
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
