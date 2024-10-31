<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\Sub;
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Sub();
        parent::__construct($this->model);
    }

    public function info(int $bidbillId) {
        if (empty($bidbillId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('bid_bill_id', $bidbillId);
        $object = $qurey->first();
        if (empty($object)) {
            return [];
        }
        $sub = $object->toArray();

        $sub['amount'] = number_format($sub['amount'], 2, '.', '');
        (new UserRepo())->setUser($sub, 'created_by', 'created_name');
        (new UserRepo())->setUser($sub, 'updated_by', 'updated_name');
        (new UserRepo())->setUser($sub, 'auditor_by', 'auditor_name');
        (new UserRepo())->setUser($sub, 'decider_by', 'decider_name');
        (new SupplierBaseRepo())->setSupplier($sub, 'supplier_id', 'supplier_name');
        (new SupplierBaseRepo)->setSupplier($sub, 'supplier_id1', 'supplier_name1');
        return $sub;
    }

    public function updateData(int $bidBillId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $id = Sub::where('bid_bill_id', $bidBillId)->value('id');
        $time = date('Y-m-d H:i:s');
        $data = [
            'id' => !empty($id) ? $id : null,
            'bid_bill_id' => $bidBillId,
        ];
        $data['updated_by'] = $userId;
        $data['updated_at'] = $time;
        if (empty($id)) {
            $data['created_by'] = $userId;
            $data['created_at'] = $time;
            return Sub::insert($data);
        }
        return Sub::where('id', $id)->update($data);
    }

    public function setSubs(&$list, $field = 'id', $winner = true) {
        if (empty($list)) {
            return;
        }
        $bidbillIds = [];
        foreach ($list as &$val) {
            $val['enroll_number'] = null;
            $val['created_at'] = null;
            $val['created_by'] = null;
            $val['created_name'] = null;
            $bidbillIds[] = $val[$field];
        }
        $fields = 'bid_bill_id,enroll_number,created_by,created_at,amount,supplier_id';
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('bid_bill_id', $bidbillIds);
        $subObjects = $qurey->get();
        if (empty($subObjects)) {
            return $list;
        }
        $subs = $subObjects->toArray();
        (new SupplierBaseRepo())->setSuppliers($subs, 'supplier_id', 'supplier_name');
        (new UserRepo)->setUsers($subs, 'created_by', 'created_name');
        $subArr = [];
        foreach ($subs as $sub) {
            $subArr[$sub['bid_bill_id']] = $sub;
        }
        foreach ($list as &$val) {
            if (empty($val[$field]) || !isset($subArr[$val[$field]])) {
                continue;
            }
            $sub = $subArr[$val[$field]];
            $val['amount'] = number_format($sub['amount'], 2, '.', '');
            if ($winner) {
                $val['supplier_name'] = $sub['supplier_name'];
                $val['supplier_id'] = $sub['supplier_id'];
            }
            $val['enroll_number'] = $sub['enroll_number'];
            $val['created_by'] = $sub['created_by'];
            $val['created_at'] = $sub['created_at'];
            $val['created_name'] = $sub['created_name'];
        }
    }

}
