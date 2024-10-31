<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\UserSupplier;

class UserSupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new UserSupplier();
        parent::__construct($this->model);
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setSuppliers(array &$list, string $field = 'user_id') {
        if (empty($list)) {
            return;
        }
        $userIds = [];
        foreach ($list as &$val) {
            $val['user_supplier'] = [];
            if ($val['user_type'] !== 'SUPPLIER') {
                continue;
            }
            if (isset($val[$field]) && $val[$field]) {
                $userIds[] = $val[$field];
            }
        }
        if (empty($userIds)) {
            return $list;
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->whereIn('user_id', $userIds);
        $supplierObjects = $qurey
                ->get();
        if (empty($supplierObjects)) {
            return $list;
        }

        $suppliers = $supplierObjects->toArray();
        (new SupplierRepo)->setSuppliers($suppliers, 'supplier_id', 'supplier_name', true);
        $supplierArr = [];
        foreach ($suppliers as $supplier) {
            $supplierArr[$supplier['user_id']] = $supplier;
        }
        foreach ($list as &$val) {
            if ($val['user_type'] !== 'SUPPLIER') {
                continue;
            }
            if (isset($val[$field]) && isset($supplierArr[$val[$field]])) {
                $val['user_supplier'] = $supplierArr[$val[$field]];
            }
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setSupplier(array &$data, string $field = 'user_id') {
        if (empty($data) || $data['user_type'] !== 'SUPPLIER') {
            return;
        }
        $data['user_supplier'] = [];
        if (isset($data[$field]) && $data[$field]) {
            $userId = $data[$field];
        }
        if (empty($userId)) {
            return $data;
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('user_id', $userId);
        $data['user_supplier'] = $qurey
                ->first()
                ->toArray();
        (new SupplierRepo)->setSupplier($data['user_supplier'], 'supplier_id', 'supplier_name', true);
        return $data;
    }

}
