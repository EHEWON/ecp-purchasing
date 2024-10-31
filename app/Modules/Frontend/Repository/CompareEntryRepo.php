<?php

/**
 * Created by PhpStorm.
 * User: wangjh
 * Date: 2018/1/24
 * Time: 08:44
 */

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Compare\Entry AS CompareEntry,
    Inquiry\Entry AS InquiryEntry,
    Quote\Quote
};
use App\Modules\Admin\Repository\{
    UnitRepo,
    SupplierBaseRepo
};

class CompareEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new CompareEntry();
        parent::__construct($this->model);
    }

    public function getList(int $inquiryId) {
        if (empty($inquiryId)) {
            return [];
        }
        $quoteTable = (new Quote)->getTable();
        $entryTable = $this->model->getTable();
        $ientryTable = (new InquiryEntry)->getTable();

        $qurey = $this->model
                ->from($ientryTable . ' as e')
                ->join($entryTable . ' AS ce', function($join) {
                    $join->on('ce.inquiry_entry_id', '=', 'e.id');
                })
                ->join($quoteTable . ' AS q', function($join) {
                    $join->on('ce.quote_id', '=', 'q.id')
                    ->on('e.inquiry_id', '=', 'q.inquiry_id');
                })
                ->selectRaw('e.inquiry_unit_id,e.material_name,e.material_desc,'
                . 'ce.adopt_flag,ce.quote_id,ce.qty,q.supplier_id,ce.inquiry_entry_id');
        $qurey->where('q.inquiry_id', $inquiryId);
        $qurey->where('ce.adopt_flag', 'true');
        $qurey->whereRaw('q.id IS NOT NULL');
        $qurey->whereRaw('ce.id IS NOT NULL');
        $qurey->where('q.deleted_flag', 'N');
        $qurey->where('ce.deleted_flag', 'N');
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id');

        foreach ($list as &$item) {
            $item['qty'] = number_format($item['qty'], 4, '.', '');
        }
        return $list;
    }

}
