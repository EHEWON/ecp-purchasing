<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\SupplierContact;
use App\Modules\Admin\Repository\SupplierBaseRepo;
use Illuminate\Http\Request;

class SupplierContactRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierContact();
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

    public function updateData(int $supplierId, Request $request) {
        SupplierContact::where('supplier_id', $supplierId)->delete();
        $contactList = $this->getContacts($supplierId, $request);
        if (!empty($contactList)) {
            SupplierContact::insert($contactList);
        }
    }

    public function getContacts(int $supplierId, Request $request) {
        $contactList = [];
        if (!empty($request->contact)) {
            foreach ($request->contact as $contact) {
                $contactList[] = [
                    'supplier_id' => $supplierId,
                    'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
                    'phone' => !empty($contact['phone']) ? $contact['phone'] : '',
                    'email' => !empty($contact['email']) ? $contact['email'] : '',
                    'remarks' => !empty($contact['remarks']) ? $contact['remarks'] : '',
                    'default_flag' => !empty($contact['default_flag']) ? $contact['default_flag'] : 'N',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $contactList;
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setDefaultContacts(array &$list, string $field = 'supplier_id') {
        if (empty($list)) {
            return;
        }
        $supplierIds = [];
        foreach ($list as &$val) {
            $val['contact_name'] = '';
            $val['contact_phone'] = '';
            $val['contact_email'] = '';
            if (isset($val[$field]) && $val[$field]) {
                $supplierIds[] = $val[$field];
            }
        }

        if (empty($supplierIds)) {
            return $list;
        }
        $qurey = $this->model
                ->select('supplier_id', 'contact_name', 'phone', 'email')
                ->where('deleted_flag', 'N')
                ->where('default_flag', 'Y');
        $qurey->whereIn('supplier_id', $supplierIds);
        $contactObjects = $qurey->get();
        if (empty($contactObjects)) {
            return $list;
        }
        $contacts = $contactObjects->toArray();
        $contactArr = [];
        foreach ($contacts as $contact) {
            $contactArr[$contact['supplier_id']] = $contact;
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($contactArr[$val[$field]])) {
                $val['contact_name'] = $contactArr[$val[$field]]['contact_name'];
                $val['contact_phone'] = $contactArr[$val[$field]]['phone'];
                $val['contact_email'] = $contactArr[$val[$field]]['email'];
            }
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc 获取行业
     */
    public function setDefaultContact(array &$arr, string $field = 'supplier_id') {
        if (empty($arr)) {
            return;
        }
        $supplierId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $supplierId = $arr[$field];
        }
        $arr['contact_name'] = '';
        $arr['contact_phone'] = '';
        if (empty($supplierId)) {
            return $arr;
        }
        $contact = $this->model
                ->select('supplier_id', 'contact_name', 'phone', 'email')
                ->where('deleted_flag', 'N')
                ->where('default_flag', 'Y')
                ->where('supplier_id', $supplierId)
                ->first();
        if (empty($contact)) {
            return $arr;
        }
        $arr['contact_name'] = $contact['contact_name'];
        $arr['contact_phone'] = $contact['phone'];
        $arr['contact_email'] = $contact['email'];
        return;
    }

    /**
     * Description of 获取创建人姓名
     * @param array $supplieId
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function getDefaultContact(string $supplieId) {
        if (empty($supplieId)) {
            return[];
        }
        $qurey = $this->model
                ->select('supplier_id', 'contact_name', 'phone', 'email')
                ->where('deleted_flag', 'N')
                ->where('default_flag', 'Y');
        $qurey->where('supplier_id', $supplieId);
        $contactObjects = $qurey->first();
        if (empty($contactObjects)) {
            return [];
        }
        $data = $contactObjects->toArray();
        (new SupplierBaseRepo)->setSupplier($data);
        return $data;
    }

}
