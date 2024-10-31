<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\BidBillQuote;
use App\Modules\Admin\Repository\SupplierBaseRepo;
use Illuminate\Http\Request;

class QuoteRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBillQuote();
        parent::__construct($this->model);
    }

    public function getList(int $bidBillId) {
        if (empty($bidBillId)) {
            return [];
        }
        $qurey = $this->model
                ->selectRaw('*');
        $qurey->where('bid_bill_id', $bidBillId);
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($data);
        return $data;
    }

    public function updateData(int $bidBillId, Request $request) {
        Supplier::where('bid_bill_id', $bidBillId)->delete();
        $attachList = $this->getSuppliers($bidBillId, $request);
        if (!empty($attachList)) {
            Supplier::insert($attachList);
        }
    }

    public function getSuppliers(int $bidBillId, Request $request) {
        $supplierList = [];
        if (!empty($request->quotes)) {
            foreach ($request->suppliers as $key => $supplier) {
                $supplierList[] = [
                    'bid_bill_id' => $bidBillId,
                    'seq' => !empty($supplier['seq']) ? $supplier['seq'] : $key + 1,
                    'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : '0',
                    'amount' => !empty($supplier['amount']) ? $supplier['amount'] : 0,
                    'quote_date' => !empty($supplier['quote_date']) ? $supplier['quote_date'] : date('Y-m-d'),
                    'reduceamt' => !empty($supplier['reduceamt']) ? $supplier['reduceamt'] : '0',
                    'sup_preduceamt' => !empty($supplier['sup_preduceamt']) ? $supplier['sup_preduceamt'] : '0',
                    'quo_supp_amount' => !empty($supplier['quo_supp_amount']) ? $supplier['quo_supp_amount'] : '0',
                    'exchange' => !empty($supplier['exchange']) ? $supplier['exchange'] : '1',
                    'quote_tax_price' => !empty($supplier['quote_tax_price']) ? $supplier['quote_tax_price'] : 0,
                    'quote_price' => !empty($supplier['quote_price']) ? $supplier['quote_price'] : null,
                    'material_name' => !empty($supplier['material_name']) ? $supplier['material_name'] : '',
                    'material_name_text' => !empty($supplier['material_name_text']) ? $supplier['material_name_text'] : '',
                ];
            }
        }
        return $supplierList;
    }

    public function getEntryStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '已报价';
            case 'B':
                return '已开标';
            case 'C':
                return '已采纳';
            case 'D':
                return '部分采纳';
            case 'E':
                return '未采纳';
            case 'F':
                return '不报价';
            default:
                return '未参与';
        }
    }

    public function getEntryStatusList() {
        return [
            'A' => '已报价',
            'B' => '已开标',
            'C' => '已采纳',
            'D' => '部分采纳',
            'E' => '未采纳',
            'F' => '不报价',
        ];
    }

    public function getBizStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '待报价';
            case 'B':
                return '已报价';
            case 'C':
                return '不报价';
            case 'D':
                return '未参与';
            case 'E':
                return '已终止';
            default:
                return '待报价';
        }
    }

    public function getBizStatusList() {
        return [
            'A' => '待报价',
            'B' => '已报价',
            'C' => '不报价',
            'D' => '未参与',
            'E' => '已终止',
        ];
    }

}
