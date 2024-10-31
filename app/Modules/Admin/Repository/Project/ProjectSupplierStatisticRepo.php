<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\ProjectSupplierStatistic;

class ProjectSupplierStatisticRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectSupplierStatistic();
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
    public function setSuppliers(array &$list, string $field = 'supplier_id', string $orgId = null) {
        if (empty($list) || empty($orgId)) {
            return;
        }
        $supplierIds = [];
        foreach ($list as &$val) {
            $val['winning_num'] = '0';
            $val['nomination_num'] = '0';
            $val['invited_num'] = '0';
            if (isset($val[$field]) && $val[$field]) {
                $supplierIds[] = $val[$field];
            }
        }
        if (empty($supplierIds)) {
            return $list;
        }
        $qurey = $this->model->select('supplier_id', 'won_qty', 'nomination_qty', 'invited_qty', 'org_id');
        $qurey->whereIn('supplier_id', $supplierIds);
        $supplierObjects = $qurey->get();
        if (empty($supplierObjects)) {
            return $list;
        }
        $suppliers = $supplierObjects->toArray();
        $supplierArr = [];
        foreach ($suppliers as $supplier) {
            $supplierArr[$supplier['org_id']][$supplier['supplier_id']] = $supplier;
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && !empty($supplierArr[$orgId]) && isset($supplierArr[$orgId][$val[$field]])) {
                $supplier = $supplierArr[$orgId][$val[$field]];
                $val['winning_num'] = $supplier['won_qty'];
                $val['nomination_num'] = $supplier['nomination_qty'];
                $val['invited_num'] = $supplier['invited_qty'];
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
    public function setSupplier(array &$arr, string $field = 'supplier_id', string $orgId = null) {
        if (empty($arr) || empty($orgId)) {
            return;
        }
        $supplierId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $supplierId = $arr[$field];
        }

        $arr['winning_num'] = '0';
        $arr['nomination_num'] = '0';
        $arr['invited_num'] = '0';
        if (empty($supplierId)) {
            return $arr;
        }
        $supplierObj = $this->model
                ->select('supplier_id', 'won_qty', 'nomination_qty', 'invited_qty')
                ->where('supplier_id', $supplierId)
                ->where('org_id', $orgId)
                ->first();
        if (empty($supplierObj)) {
            return;
        }
        $arr['winning_num'] = $supplierObj->won_qty;
        $arr['nomination_num'] = $supplierObj->nomination_qty;
        $arr['invited_num'] = $supplierObj->invited_qty;
    }

}
