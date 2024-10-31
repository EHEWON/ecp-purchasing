<?php

namespace App\Modules\Admin\Repository\Quote;

use App\Common\Contracts\Repository;
use App\Common\Models\Quote\QuoteAttach;
use Illuminate\Http\Request;

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
        return $qurey->orderBy('id', 'ASC')->get();
    }

    public function updateData(int $quoteId, Request $request) {
        Attach::where('quote_id', $quoteId)->delete();
        $attachList = $this->getAttachs($quoteId, $request);
        if (!empty($attachList)) {
            Attach::insert($attachList);
        }
    }

    public function getAttachs(int $quoteId, Request $request) {
        $attachList = [];
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
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $attachList;
    }

}
