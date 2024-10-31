<?php

namespace App\Modules\Admin\Repository\Compare;

use App\Common\Contracts\Repository;
use App\Common\Models\Compare\Attach;
use Illuminate\Http\Request;

class AttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Attach();
        parent::__construct($this->model);
    }

    public function getList(int $compareId) {
        if (empty($compareId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('compare_id', $compareId);
        $qurey->where('deleted_flag', 'N');
        return $qurey->orderBy('id', 'ASC')->get();
    }

    public function updateData(int $compareId, Request $request) {
        Attach::where('compare_id', $compareId)->delete();
        $attachList = $this->getAttachs($compareId, $request);
        if (!empty($attachList)) {
            Attach::insert($attachList);
        }
    }

    public function getAttachs(int $compareId, Request $request) {
        $attachList = [];
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'compare_id' => $compareId,
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
