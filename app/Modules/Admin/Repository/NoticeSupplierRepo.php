<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\NoticeSupplier;
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    Inquiry\SupplierRepo as ISupplierRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeSupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new NoticeSupplier();
        parent::__construct($this->model);
    }

    public function getList(int $noticeId) {
        if (empty($noticeId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('notice_id', $noticeId);
        $qurey->where('deleted_flag', 'N');
        $objct = $qurey->orderBy('id', 'ASC')
                ->get();
        if (empty($objct)) {
            return [];
        }
        $list = $objct->toArray();
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id');
        return $list;
    }

    public function setReadFlags(&$list, $supplierId) {
        if (empty($list)) {
            return;
        }
        $noticeIds = [];
        foreach ($list as &$val) {
            $val['read_flag'] = 'N';
            $noticeIds[] = $val['id'];
        }
        if (empty($noticeIds)) {
            return $list;
        }

        $qurey = $this->model
                ->selectRaw('count(id) AS num,notice_id');
        $qurey->whereIn('notice_id', $noticeIds);
        $qurey->where('supplier_id', $supplierId);
        $noticeSupplierObjects = $qurey
                        ->groupBy('notice_id')->get();
        if (empty($noticeSupplierObjects)) {
            return $list;
        }

        $noticeSuppliers = $noticeSupplierObjects->toArray();
        $noticeSupplierArr = [];
        foreach ($noticeSuppliers as $ns) {
            $noticeSupplierArr[$ns['notice_id']] = $ns['num'];
        }
        foreach ($list as &$val) {
            if (isset($noticeSupplierArr[$val['id']])) {
                $val['read_flag'] = 'Y';
            }
        }
    }

    public function setReadFlag(&$data, $supplierId) {
        if (empty($data)) {
            return;
        }
        $data['read_flag'] = 'N';
        $noticeId = $data['id'];

        if (empty($noticeId)) {
            return $data;
        }

        $qurey = $this->model
                ->select('count(id) AS num,notice_id');
        $qurey->where('notice_id', $noticeId);
        $qurey->where('supplier_id', $supplierId);
        $count = $qurey->count();
        if (empty($count)) {
            $data['read_flag'] = 'Y';
        }
    }

    public function updateData(int $noticeId, Request $request) {
        NoticeSupplier::where('notice_id', $noticeId)->delete();
        $supplierList = $this->getSuppliers($noticeId, $request);
        if (!empty($supplierList)) {
            NoticeSupplier::insert($supplierList);
        }
    }

    public function getSuppliers(int $noticeId, Request $request) {
        $dataList = [];
        $admin = Auth::guard('admin')->user();
        switch (strtolower($request->src_bill_type)) {
            case 'sou_inquiry':
                $supplierList = (new ISupplierRepo)->getList($request->src_bill_id);
                if (empty($supplierList)) {
                    return;
                }
                foreach ($supplierList as $key => $supplier) {
                    $dataList[] = [
                        'notice_id' => $noticeId,
                        'seq' => $key + 1,
                        'supplier_id' => $supplier['supplier_id'],
                        'contacter' => $supplier['contact_name'],
                        'phone' => $supplier['contact_phone'],
                        'email' => $supplier['contact_email'],
                        'created_by' => $admin->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
                return $dataList;
            case 'sou_compare':
                return $dataList;
        }
    }

}
