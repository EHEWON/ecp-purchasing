<?php

namespace App\Modules\Admin\Repository\Quote;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Entry,
    Quote\Quote,
    Quote\QuoteEntry,
    Quote\QuoteEntrySub
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    SupplierBaseRepo,
    TaxRateRepo,
    UnitRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new QuoteEntry();
        parent::__construct($this->model);
    }

    public function getList(int $quoteId) {
        if (empty($quoteId)) {
            return [];
        }
//        $entrySubTable = (new EntrySub)->getTable();
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.material_name,e.qty,e.deli_address,'
                . 'e.delive_method,e.deli_at,e.inquiry_entry_id,e.tax_amount,'
                . 'e.tax_rate,e.price,e.tax_price,e.amount,e.tax_rate_id,e.tax,'
                . 'e.pobill_no,e.pcbill_no,e.quote_curr,e.`precision`,e.stock_code,'
                . 'e.exrate,e.warranty_period,e.inquire_qty as new_qty,e.note,'
                . 'e.price_field,e.boss_goods_id,e.specification_model,e.spec_model,'
                . 'e.boss_goods,e.material_desc,'
                . 'e.created_at,e.created_by,e.inquiry_unit_id,e.quote_unit_id');

        $qurey->where('e.quote_id', $quoteId);
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['tax_rate_id'] = !empty($item['tax_rate_id']) ? $item['tax_rate_id'] : null;
            $item['price'] = number_format($item['price'], 4, '.', '');
            $item['tax_price'] = number_format($item['tax_price'], 4, '.', '');
            $item['new_qty'] = number_format($item['new_qty'], $item['precision']);
            $item['tax_rate'] = number_format($item['tax_rate'], 2, '.', '');
            $item['amount'] = number_format($item['amount'], 2, '.', '');
            $item['tax'] = number_format($item['tax'], 2, '.', '');
            $item['tax_amount'] = number_format($item['tax_amount'], 2, '.', '');
            $item['qty'] = number_format($item['qty'], $item['precision'], '.', '');
        }
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');
        (new UnitRepo)->setUnits($list, 'quote_unit_id', 'quote_unit_name');
        (new TaxRateRepo)->setTaxRates($list, 'tax_rate_id', 'tax_rate_name');
        return $list;
    }

    public function updateData(int $quoteId, Request $request) {
        $admin = Auth::guard('admin')->user();
        Entry::where('quote_id', $quoteId)->delete();
        if (!empty($request->entrys)) {
            foreach ($request->entrys as $key => $entry) {
                $entryData = [
                    'quote_id' => $quoteId,
                    'seq' => $key + 1,
                    'material_id' => !empty($entry['material_id']) ? $entry['material_id'] : null,
                    'material_desc' => !empty($entry['material_desc']) ? $entry['material_desc'] : null,
                    'asstpro_id' => !empty($entry['asstpro_id']) ? $entry['asstpro_id'] : null,
                    'unit_id' => !empty($entry['unit_id']) ? $entry['unit_id'] : null,
                    'qty' => !empty($entry['qty']) ? $entry['qty'] : null,
                    'deli_date' => !empty($entry['deli_date']) ? $entry['deli_date'] : null,
                    'deli_addr' => !empty($entry['deli_addr']) ? $entry['deli_addr'] : null,
                    'deli_type_id' => !empty($entry['deli_type_id']) ? $entry['deli_type_id'] : 0,
                    'price' => !empty($entry['price']) ? $entry['price'] : 0.000000,
                    'tax_price' => !empty($entry['tax_price']) ? $entry['tax_price'] : 0.000000,
                    'dct_rate' => !empty($entry['dct_rate']) ? $entry['dct_rate'] : 0.000000,
                    'dct_amount' => !empty($entry['dct_amount']) ? $entry['dct_amount'] : 0.000000,
                    'amount' => !empty($entry['amount']) ? $entry['amount'] : 0.000000,
                    'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0.000000,
                    'tax' => !empty($entry['tax']) ? $entry['tax'] : 0.000000,
                    'tax_amount' => !empty($entry['tax_amount']) ? $entry['tax_amount'] : 0.000000,
                    'req_org_id' => !empty($entry['req_org_id']) ? $entry['req_org_id'] : 0,
                    'pur_org_id' => !empty($entry['pur_org_id']) ? $entry['pur_org_id'] : 0,
                    'rcv_org_id' => !empty($entry['rcv_org_id']) ? $entry['rcv_org_id'] : 0,
                    'settle_org_id' => !empty($entry['settle_org_id']) ? $entry['settle_org_id'] : 0,
                    'pay_org_id' => !empty($entry['pay_org_id']) ? $entry['pay_org_id'] : 0,
                    'note' => !empty($entry['note']) ? $entry['note'] : '',
                    'entry_status' => !empty($entry['entry_status']) ? $entry['entry_status'] : '',
                    'po_bill_no' => !empty($entry['po_bill_no']) ? $entry['po_bill_no'] : '',
                    'pc_bill_no' => !empty($entry['pc_bill_no']) ? $entry['pc_bill_no'] : '',
                    'project_id' => !empty($entry['project_id']) ? $entry['project_id'] : 0,
                    'trace_id' => !empty($entry['trace_id']) ? $entry['trace_id'] : 0,
                    'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : 0,
                    'inquiry_unit_id' => !empty($entry['inquiry_unit_id']) ? $entry['inquiry_unit_id'] : null,
                    'quote_unit_id' => !empty($entry['quote_unit_id']) ? $entry['quote_unit_id'] : null,
                    'quote_qty' => !empty($entry['quote_qty']) ? $entry['quote_qty'] : null,
                    'material_name' => !empty($entry['material_name']) ? $entry['material_name'] : null,
                    'specification_model' => !empty($entry['specification_model']) ? $entry['specification_model'] : null,
                    'deli_at' => !empty($entry['deli_at']) ? $entry['deli_at'] : null,
                    'deli_address' => !empty($entry['deli_address']) ? $entry['deli_address'] : null,
                    'delive_method' => !empty($entry['delive_method']) ? $entry['delive_method'] : null,
                    'inquire_qty' => !empty($entry['inquire_qty']) ? $entry['inquire_qty'] : null,
                    'valid_num' => !empty($entry['valid_num']) ? $entry['valid_num'] : 0,
                    'big_note' => !empty($entry['big_note']) ? $entry['big_note'] : null,
                    'big_note_tag' => !empty($entry['big_note_tag']) ? $entry['big_note_tag'] : null,
                    'supplier_id' => !empty($entry['supplier_id']) ? $entry['supplier_id'] : null,
                    'new_tax_rate_id' => !empty($entry['new_tax_rate_id']) ? $entry['new_tax_rate_id'] : null,
                    'new_qty' => !empty($entry['new_qty']) ? $entry['new_qty'] : null,
                    'new_tax_amount' => !empty($entry['new_tax_amount']) ? $entry['new_tax_amount'] : null,
                    'stock_code' => !empty($entry['stock_code']) ? $entry['stock_code'] : null,
                    'text_field' => !empty($entry['text_field']) ? $entry['text_field'] : null,
                    'brand' => !empty($entry['brand']) ? $entry['brand'] : null,
                    'material_name_text' => !empty($entry['material_name_text']) ? $entry['material_name_text'] : null,
                    'line_type_id' => !empty($entry['line_type_id']) ? $entry['line_type_id'] : 0,
                    'base_data_field' => !empty($entry['base_data_field']) ? $entry['base_data_field'] : null,
                    'material' => !empty($entry['material']) ? $entry['material'] : null,
                    'budget_price' => !empty($entry['budget_price']) ? $entry['budget_price'] : null,
                    'budget_amount' => !empty($entry['budget_amount']) ? $entry['budget_amount'] : null,
                    'amount_field' => !empty($entry['amount_field']) ? $entry['amount_field'] : null,
                    'new_material_code' => !empty($entry['new_material_code']) ? $entry['new_material_code'] : null,
                    'boss_goods_id' => !empty($entry['boss_goods_id']) ? $entry['boss_goods_id'] : null,
                    'material_code' => !empty($entry['material_code']) ? $entry['material_code'] : null,
                    'quote_curr' => !empty($entry['quote_curr']) ? $entry['quote_curr'] : 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $admin->user_id,
                ];
                return Entry::insertGetId($entryData);
            }
        }
    }

    public function details(int $inquiryId, $inquiry) {
        if (empty($inquiryId)) {
            return [[], [], []];
        }
        $inquiryEntryTable = (new Entry)->getTable();
        $quoteTable = (new Quote)->getTable();
        $subTable = (new QuoteEntrySub)->getTable();
        $totalInquiry = $inquiry->total_inquiry;
//        $turns = $inquiry->turns;
        $bizStatus = $inquiry->biz_status;
        $entryTable = $this->model->getTable();
        $quoteQuery = Quote::from($quoteTable . ' as sq')
                ->selectRaw('sq.inquiry_id,sq.supplier_id,max(sq.turns) as max_turns,max(sq.id) AS max_quote_id')
                ->where('sq.bill_status', 'C')
                ->where('sq.deleted_flag', 'N')
                ->orderBy('sq.turns', 'DESC')
                ->groupBy('sq.inquiry_id')
                ->groupBy('sq.supplier_id');
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->join($inquiryEntryTable . ' as ie', function($join) {
                    $join->on('ie.id', '=', 'e.inquiry_entry_id');
                })
                ->leftJoin($subTable . ' as es', function($join) {
                    $join->on('e.id', '=', 'es.entry_id');
                })
                ->join($quoteTable . ' as q', function($join) {
                    $join->on('q.id', '=', 'e.quote_id');
                })
                ->joinSub($quoteQuery, 'max', function ($join) {
                    $join->on('q.inquiry_id', '=', 'max.inquiry_id')
                    ->on('q.turns', '=', 'max.max_turns')
                    ->on('q.id', '=', 'max.max_quote_id')
                    ->on('q.supplier_id', '=', 'max.supplier_id');
                })
                ->selectRaw('e.id,e.qty,e.deli_address,e.warranty_period,'
                . 'e.delive_method,e.deli_at,e.inquiry_entry_id,'
                . 'e.tax_rate,e.price,e.tax_price,e.amount,'
                . 'e.pobill_no,e.pcbill_no,e.quote_curr,'
                . 'ie.stock_code AS ie_stock_code,ie.specification_model as ie_specification_model,'
                . 'e.exrate,e.warranty_period,'
                . 'e.created_at,e.created_by,e.`precision`,'
                . 'ie.material_name,q.supplier_id,e.note,'
                . 'ie.inquire_qty,e.quote_unit_id,'
                . 'ie.inquiry_unit_id,e.stock_code, e.specification_model,e.spec_model,'
                . 'ie.material_desc,es.cfm_price,'
                . 'es.cfm_qty,cfm_tax_price,cfm_tax_rate,cfm_note,'
                . 'q.biz_status,e.quote_id'
        );
        $qurey->where('q.inquiry_id', $inquiryId);
//        $qurey->where('q.turns', $turns);

        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey
//          ->where('e.price', '>', '0')
//          ->where('e.tax_price', '>', '0')
                ->where('q.bill_status', 'C')
                ->orderBy('q.bill_date', 'ASC')
                ->orderBy('e.inquiry_entry_id', 'ASC')
                ->orderBy('q.supplier_id', 'ASC')
                ->get();
        if (empty($object)) {
            return [[], [], []];
        }
        $list = $object->toArray();
        (new CurrencyRepo)->setCurrencys($list, 'quote_curr', ['quote_curr_name' => 'name',
            'quote_curr_sign' => 'sign',
            'quote_curr_number' => 'number',
        ]);
        (new UnitRepo)->setUnits($list, 'quote_unit_id', 'quote_unit_name');
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        $ret = [];
        foreach ($list as $item) {
            $item['inquire_qty'] = number_format($item['inquire_qty'], 2, '.', '');
            if (empty($ret[$item['inquiry_entry_id']])) {
                $ret[$item['inquiry_entry_id']] = [
                    'material_name' => $item['material_name'],
                    'inquire_qty' => $item['inquire_qty'],
                    'inquiry_unit_name' => $item['inquiry_unit_id_name'],
                    'material_desc' => $item['material_desc'],
                    'stock_code' => $item['ie_stock_code'],
                    'specification_model' => $item['ie_specification_model'],
                ];
            }
            $item['tax_rate'] = number_format($item['tax_rate'], 2, '.', '');
            $item['cfm_tax_rate'] = !empty($item['cfm_tax_rate']) ? $item['cfm_tax_rate'] : $item['tax_rate'];
            $item['amount'] = number_format($item['amount'], 2, '.', '');
            $item['exrate'] = number_format($item['exrate'], 8, '.', '');
            $item['supplier_id'] = (string) $item['supplier_id'];
            $item['cfm_price'] = !empty($item['cfm_price']) ? $item['cfm_price'] : $item['price'];
            $item['cfm_qty'] = !empty($item['cfm_qty']) ? $item['cfm_qty'] : $item['qty'];
            $item['cfm_tax_price'] = !empty($item['cfm_tax_price']) ? $item['cfm_tax_price'] : $item['tax_price'];
            $item['price'] = number_format($item['price'], 4, '.', '');
            $item['tax_price'] = number_format($item['tax_price'], 4, '.', '');
            $item['cfm_amount'] = round($item['cfm_price'] * $item['cfm_qty'], 4);
            $item['cfm_tax_amount'] = round($item['cfm_tax_price'] * $item['cfm_qty'], 4);
            $item['cfm_tax'] = round($item['cfm_tax_amount'] - $item['cfm_amount'], 4);
            $item['cfm_price'] = number_format($item['cfm_price'], 4, '.', '');
            $item['cfm_qty'] = number_format($item['cfm_qty'], intval($item['precision']), '.', '');
            $item['cfm_tax_price'] = number_format($item['cfm_tax_price'], 4, '.', '');
            $item['cfm_tax'] = number_format($item['cfm_tax'], 2, '.', '');
            $item['qty'] = number_format($item['qty'], intval($item['precision']), '.', '');
            if ($totalInquiry == '1') {
                $item['cfm_tax_amount'] = number_format($item['cfm_tax_amount'], 2, '.', '');
                $item['cfm_amount'] = number_format($item['cfm_amount'], 2, '.', '');
                $ret[$item['inquiry_entry_id']]['quotes'][] = $item;
                continue;
            }
            $ret[$item['inquiry_entry_id']]['quotes'][] = $item;
        }
        $result = [];
        $supplierIds = [];
        $sumAmount = [];
        foreach ($ret as $item) {
            if ($totalInquiry == '1') {
                $result[] = $item;
                continue;
            }
            $entrys = $item['quotes'];
            $minSumAmount = $this->getMinCfmTaxAmount($entrys);
            $acceptFlag = false;
            foreach ($entrys as &$entry) {
                $supplierId = $entry['supplier_id'];
                $qbizStatus = $entry['biz_status'];
                if ($qbizStatus === 'C' || $qbizStatus === 'D') {
                    $entry['adopt_flag'] = true;
                    $supplierIds[] = $entry['supplier_id'];
                    $sumAmount[$entry['supplier_id']] = (!empty($sumAmount[$supplierId]) ? $sumAmount[$supplierId] : 0) + $entry['cfm_amount'];
                    continue;
                } elseif ($qbizStatus === 'E') {
                    $entry['adopt_flag'] = false;
                    $entry['adopt_total_amount'] = null;
                    continue;
                } elseif ($bizStatus !== 'B') {
                    $entry['adopt_flag'] = false;
                    $entry['adopt_total_amount'] = null;
                    continue;
                } elseif ($acceptFlag === true || $entry['cfm_tax_amount'] == 0) {
                    $entry['adopt_flag'] = false;
                    $entry['adopt_total_amount'] = null;
                    continue;
                } elseif ($acceptFlag === true || $entry['cfm_tax_amount'] !== $minSumAmount) {
                    $entry['adopt_flag'] = false;
                    $entry['adopt_total_amount'] = null;
                    continue;
                }
                $supplierIds[] = $entry['supplier_id'];
                $entry['adopt_flag'] = true;
                $entry['adopt_total_amount'] = number_format($minSumAmount, 2, '.', '');
                $sumAmount[$entry['supplier_id']] = (!empty($sumAmount[$supplierId]) ? $sumAmount[$supplierId] : 0) + $entry['cfm_tax_amount'];
                $acceptFlag = true;
                $entry['cfm_tax_amount'] = number_format($entry['cfm_tax_amount'], 2, '.', '');
                $entry['cfm_amount'] = number_format($entry['cfm_amount'], 2, '.', '');
            }
            $item['quotes'] = $entrys;
            $result[] = $item;
        }
        return[$result, $supplierIds, $sumAmount];
    }

    public function getMinCfmTaxAmount($entrys) {
        $cfmTaxAmountArr = array_column($entrys, 'cfm_tax_amount');
        $minCfmTaxAmount = null;
        foreach ($cfmTaxAmountArr as $cfmTaxAmount) {
            if ($cfmTaxAmount == 0) {
                continue;
            }
            if (empty($minCfmTaxAmount)) {
                $minCfmTaxAmount = $cfmTaxAmount;
                continue;
            }
            if ($cfmTaxAmount > 0 && $minCfmTaxAmount > $cfmTaxAmount) {
                $minCfmTaxAmount = $cfmTaxAmount;
            }
        }
        return $minCfmTaxAmount;
    }

    public function setEntrys(&$list) {
        if (empty($list)) {
            return;
        }
        $quoteIds = [];
        foreach ($list as &$quote) {
            $quote['entrys'] = [];
            $quoteIds[] = $quote['id'];
        }
        if (empty($quoteIds)) {
            return;
        }
        $entry = (new Entry)->getTable();
        $quoteEntry = (new QuoteEntry)->getTable();
        $entryObj = $this->model
                ->from($quoteEntry . ' AS qe')
                ->selectRaw('qe.quote_id,qe.material_name,qe.material_desc,qe.inquire_qty,qe.qty,qe.specification_model,'
                        . 'qe.inquiry_unit_id,qe.quote_unit_id,qe.tax_rate,qe.tax_price,qe.price,qe.tax,qe.amount,'
                        . 'qe.tax_amount,qe.warranty_period,qe.stock_code')
                ->whereIn('qe.quote_id', $quoteIds)
                ->where('qe.deleted_flag', 'N')
                ->get();
        if (empty($entryObj)) {
            return;
        }
        $entryList = $entryObj->toArray();
        (new UnitRepo)->setUnits($entryList, 'quote_unit_id', 'quote_unit_name');
        (new UnitRepo)->setUnits($entryList, 'inquiry_unit_id', 'inquiry_unit_name');
        $entryArr = [];
        foreach ($entryList as $entry) {
            $entryArr[$entry['quote_id']][] = $entry;
        }
        foreach ($list as &$quote) {
            if (empty($entryArr[$quote['id']])) {
                continue;
            }
            $quote['entrys'] = $entryArr[$quote['id']];
        }
    }

}
