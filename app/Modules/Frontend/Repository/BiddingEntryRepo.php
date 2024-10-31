<?php

/**
 * Created by PhpStorm.
 * User: wangjh
 * Date: 2018/1/24
 * Time: 08:44
 */

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\{
    BidBillEntry,
   Sub AS BidBillSub
};
use App\Modules\Admin\Repository\{
    UnitRepo,
    SupplierBaseRepo
};
class BiddingEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBillEntry();
        parent::__construct($this->model);
    }

    public function getList(int $bidBillId) {
        if (empty($bidBillId)) {
            return [];
        }
        $subTable = (new BidBillSub)->getTable();
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->join($subTable . ' AS s', function($join) {
                    $join->on('s.bid_bill_id', '=', 'e.bid_bill_id');
                })
                ->selectRaw('e.*,s.supplier_id');
        $qurey->where('e.bid_bill_id', $bidBillId);
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        (new UnitRepo)->setUnits($list, 'unit_id', 'unit_name');
        foreach ($list as &$item) {
            $item['qty'] = number_format($item['qty'], 2, '.', '');
            $item['price'] = number_format($item['price'], 4, '.', '');
            $item['tax_price'] = number_format($item['tax_price'], 4, '.', '');
            $item['amount'] = number_format($item['amount'], 2, '.', '');
            $item['tax_rate'] = intval($item['tax_rate']);
            $item['tax'] = number_format($item['tax'], 2, '.', '');
            $item['tax_amount'] = number_format($item['tax_amount'], 2, '.', '');
        }
        return $list;
    }

}
