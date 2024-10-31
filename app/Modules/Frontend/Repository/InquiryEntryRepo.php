<?php

/**
 * Created by PhpStorm.
 * User: wangjh
 * Date: 2018/1/24
 * Time: 08:44
 */

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Inquiry\Entry AS InquiryEntry;
use App\Modules\Admin\Repository\UnitRepo;

class InquiryEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new InquiryEntry();
        parent::__construct($this->model);
    }

    public function getList(int $inquiryId) {
        if (empty($inquiryId)) {
            return [];
        }
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.id,e.material_id,e.material_desc,e.inquiry_unit_id,'
                . 'e.material_name,e.inquire_qty,e.material_code,stock_code,brand,'
                . 'e.specification_model,e.deli_type_id,e.deli_date,e.deli_addr,warranty_period,e.precision,e.boss_goods_id');
        $qurey->where('e.inquiry_id', $inquiryId);
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');

        foreach ($list as &$item) {
            $item['inquire_qty'] = number_format($item['inquire_qty'], 4, '.', '');
            $item['deli_type'] = $item['deli_type_id'] == '0' ? '发货' : '自提';
        }
        return $list;
    }

}
