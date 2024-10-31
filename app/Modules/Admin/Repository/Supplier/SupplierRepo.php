<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\Inquiry\{
    Supplier
};
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    UserRepo,
    SupplierContactRepo
};
use App\Modules\Admin\Repository\Inquiry\{
    InquiryRepo,
    SupplierRepo AS ISupplierRepo
};
use Illuminate\Support\Facades\Auth;

class SupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Supplier();
        parent::__construct($this->model);
    }

    public function getList(int $inquiryId, int $supplierId, int $entryTurns = 1, $endDate = null) {
        if (empty($inquiryId) || empty($supplierId)) {
            return [];
        }
        $admin = Auth::guard('admin')->user();
        $qurey = $this->model->selectRaw('supplier_id,contact_name,'
                . 'contact_phone,contact_email,entry_turns,entry_status,dead_line,'
                . 'supplier_biz_status,entry_count,quoter_id,quote_id,quote_date');
        $qurey->where('inquiry_id', $inquiryId);
        $qurey->where('supplier_id', $supplierId);
        $qurey->where('deleted_flag', 'N');
        $list = $qurey->orderBy('id', 'ASC')->get()->toArray();
        $inquiryRepo = new InquiryRepo();
        if (empty($list)) {
            $contact = (new SupplierContactRepo)->getDefaultContact($supplierId);
            $data = [[
            'inquiry_id' => (string) $inquiryId,
            'seq' => 0,
            'supplier_id' => (string) $supplierId,
            'quoter_id' => $admin->user_id,
            'quoter_name' => $admin->realname,
            'quote_id' => 0,
            'quote_date' => null,
            'entry_status' => '',
            'supplier_biz_status' => 'A',
            'entry_status_name' => '',
            'supplier_biz_status_name' => '待报价',
            'entry_turns' => $entryTurns,
            'entry_count' => '0',
            'can_show' => 1,
            'dead_line' => $endDate,
            'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
            'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
            'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
            'created_at' => null,
            ]];
            $data[0]['entry_turns_name'] = $data[0]['entry_turns'];
            $data[0]['entry_count_name'] = !empty($data[0]['entry_count']) ? $data[0]['entry_count'] : null;
            (new SupplierBaseRepo)->setSuppliers($data);
            return $data;
        }
        $ISupplierRepo = new ISupplierRepo();
        $userRepo = new UserRepo();
        foreach ($list as &$item) {
            $item['supplier_entry_status_name'] = $ISupplierRepo->getEntryStatusText($item['entry_status']);
            $item['supplier_biz_status_name'] = $ISupplierRepo->getBizStatusText($item['supplier_biz_status']);
            $item['entry_turns_name'] = $item['entry_turns'];
            $item['entry_count_name'] = !empty($item['entry_count']) ? $item['entry_count'] : null;
            if (empty($item['quoter_id']) && $entryTurns = $item['entry_turns']) {
                $item['quoter_id'] = $admin->user_id;
            }
        }
        $userRepo->setUsers($list, 'quoter_id', 'quoter_name');
        (new SupplierBaseRepo)->setSuppliers($list);
        return $list;
    }

    public function setSupplierStatusies(&$list, int $supplierId) {
        $inquiryIds = [];
        foreach ($list as &$item) {
            $item['supplier_entry_status'] = '';
            $item['supplier_biz_status'] = '';
            $item['supplier_entry_status_name'] = '';
            $item['supplier_biz_status_name'] = '';
            $inquiryIds[] = $item['id'];
        }
        $qurey = $this->model->selectRaw('inquiry_id,contact_name,'
                . 'contact_phone,contact_email,entry_turns,entry_status,'
                . 'supplier_biz_status,entry_count');
        $qurey->whereIn('inquiry_id', $inquiryIds);
        $qurey->where('supplier_id', $supplierId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $supplierList = $object->toArray();
        $supplierArr = [];
        foreach ($supplierList as $supplier) {
            $supplierArr[$supplier['inquiry_id']] = $supplier;
        }
        $ISupplierRepo = new ISupplierRepo();
        foreach ($list as &$item) {
            if (!empty($supplierArr[$item['id']])) {
                $item['supplier_entry_status'] = $supplierArr[$item['id']]['supplier_biz_status'];
                $item['supplier_biz_status'] = $supplierArr[$item['id']]['entry_status'];
                $item['supplier_entry_status_name'] = $ISupplierRepo->getEntryStatusText($item['supplier_entry_status']);
                $item['supplier_biz_status_name'] = $ISupplierRepo->getBizStatusText($item['supplier_biz_status']);
            }
        }
    }

    public function setSupplierStatus(&$data, int $supplierId) {

        if (empty($data)) {
            return [];
        }
        $inquiryId = $data['id'];
        $data['supplier_biz_status'] = '';
        $data['supplier_entry_status'] = '';
        $data['supplier_entry_status_name'] = '';
        $data['supplier_biz_status_name'] = '';
        $qurey = $this->model->selectRaw('supplier_id,contact_name,'
                . 'contact_phone,contact_email,entry_turns,entry_status,'
                . 'supplier_biz_status,entry_count');
        $qurey->where('inquiry_id', $inquiryId);
        $qurey->where('supplier_id', $supplierId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->first();
        if (empty($object)) {
            return [];
        }
        $ISupplierRepo = new ISupplierRepo();
        $data['supplier_biz_status'] = $object->supplier_biz_status;
        $data['supplier_entry_status'] = $object->entry_status;
        $data['supplier_entry_status_name'] = $ISupplierRepo->getEntryStatusText($object->entry_status);
        $data['supplier_biz_status_name'] = $ISupplierRepo->getBizStatusText($object->supplier_biz_status);
    }

}
