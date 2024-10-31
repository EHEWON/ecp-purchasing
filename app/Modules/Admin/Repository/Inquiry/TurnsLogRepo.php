<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use App\Common\Models\Inquiry\TurnsLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TurnsLogRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new TurnsLog();
        parent::__construct($this->model);
    }

    public function getList(int $inquiryId) {
        if (empty($inquiryId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('seq,turns,'
                . 'handler_id,handle_time,note,log_dead_line,entry_log_scope');
        $qurey->where('inquiry_id', $inquiryId);
        $qurey->where('deleted_flag', 'N');
        $qurey->whereRaw('turns IS NOT NULL AND turns<>0');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();
        foreach ($data as &$item) {
            $item['turns_text'] = $inquiryRepo->getTurnsText($item['turns']);
            $item['sup_scope'] = $inquiryRepo->getSupScopeText($item['entry_log_scope']);
        }
        (new \App\Modules\Admin\Repository\UserRepo())->setUsers($data, 'handler_id', 'handler_name');
        return $data;
    }

    public function updateData(Request $request, $turns = 2) {
        $admin = Auth::guard('admin')->user();
        TurnsLog::insert([
            'inquiry_id' => $request->id,
            'turns' => !empty(trim($turns)) ? trim($turns) + 1 : 2,
            'handler_id' => $admin->user_id,
            'handle_time' => date('Y-m-d H:i:s'),
            'log_dead_line' => $request->end_date,
            'entry_flag' => !empty($request->entry_flag) ? $request->entry_flag : 'N',
            'entry_log_scope' => 2,
            'note' => $request->note,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

}
