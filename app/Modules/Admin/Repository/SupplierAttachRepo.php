<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\SupplierAttach;
use Illuminate\Http\Request;

class SupplierAttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierAttach();
        parent::__construct($this->model);
    }

    public function getList(int $supplierId) {
        if (empty($supplierId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('supplier_id', $supplierId);
        $qurey->where('deleted_flag', 'N');
        return $qurey->orderBy('id', 'ASC')->get();
    }

    public function getQualifications(int $supplierId) {
        $ret = ['business_license' => [],
            'legal_person_id1' => [],
            'legal_person_id2' => [],
//            'opens_account_licence' => [],
            'other' => [],
        ];
        if (empty($supplierId)) {
            return $ret;
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('supplier_id', $supplierId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')
                ->get();
        if (empty($object)) {
            return $ret;
        }
        $qualifications = $object->toArray();
        foreach ($qualifications as $qualification) {
            $ret[strtolower($qualification['attach_type'])][] = $qualification;
        }
        return $ret;
    }

    public function updateData(int $supplierId, Request $request) {
        SupplierAttach::where('supplier_id', $supplierId)->delete();
        $attachList = $this->getAttachs($supplierId, $request);
        if (!empty($attachList)) {
            SupplierAttach::insert($attachList);
        }
    }

    public function getAttachs(int $supplierId, Request $request) {
        $attachList = [];
        if (!empty($request->attach)) {
            foreach ($request->attach as $attachArr) {
                foreach ($attachArr as $attach) {
                    if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                        continue;
                    }
                    if (empty($attach['attach_type'])) {
                        $attach['attach_type'] = 'OTHER';
                    }
                    $attachList[] = [
                        'supplier_id' => $supplierId,
                        'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                        'attach_type' => !empty($attach['attach_type']) ? $attach['attach_type'] : 'OTHER',
                        'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                        'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }
        return $attachList;
    }

}
