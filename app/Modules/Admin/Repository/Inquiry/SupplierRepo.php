<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Supplier,
    Inquiry\Inquiry,
    Quote\Quote
};
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    SupplierContactRepo,
    Inquiry\InquiryRepo
};
use Illuminate\Http\Request;

class SupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Supplier();
        parent::__construct($this->model);
    }

    public function supplierList(int $inquiryId, $bizStatusName = null, $bizStatus = null, &$quoteNum = 0) {
        if (empty($inquiryId)) {
            return [];
        }
        $quote = (new Quote)->getTable();
        $inquiry = Inquiry::where('id', $inquiryId)
                ->selectRaw('end_date,turns')
                ->where('deleted_flag', 'N')
                ->first();
        if (empty($inquiry)) {
            check(false, '询价单不存在');
        }
        $endDate = $inquiry->end_date;
        $turns = $inquiry->turns;
        $qurey = $this->model->selectRaw('inquiry_supplier.supplier_id,inquiry_supplier.contact_name,'
                . 'inquiry_supplier.contact_phone,inquiry_supplier.contact_email,'
                . 'inquiry_supplier.entry_turns,entry_status,dead_line,'
                . 'supplier_biz_status,entry_count,q.bill_date AS quote_date,q.sum_tax_amount,q.id AS quote_id');
        $qurey->where('inquiry_supplier.inquiry_id', $inquiryId);
        $qurey->where('inquiry_supplier.deleted_flag', 'N');

        $object = $qurey
                ->leftJoin($quote . ' as q', function ($join) {
                    $join->on('q.inquiry_id', '=', 'inquiry_supplier.inquiry_id')
                    ->on('q.id', '=', 'inquiry_supplier.quote_id')
                    ->on('q.supplier_id', '=', 'inquiry_supplier.supplier_id');
//              ->on('q.bill_date', '=', 'lastquote.max_bill_date');
                })
                ->orderBy('inquiry_supplier.entry_turns', 'DESC')
                ->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();
        $time = date('Y-m-d H:i:s');
        foreach ($data as &$item) {
            $item['entry_status'] = $bizStatus;
            $item['entry_status_name'] = $bizStatusName;
            $item['sum_tax_amount'] = !empty($item['sum_tax_amount']) ? number_format($item['sum_tax_amount'], 2, '.', ',') : null;
            if (!empty($item['quote_id'])) {
                $item['supplier_biz_status'] = 'B';
                $item['biz_status_name'] = '已报价';
                $quoteNum += 1;
            } elseif ($endDate > $time && $turns > $item['entry_turns']) {
                $item['supplier_biz_status'] = 'D';
                $item['biz_status_name'] = '未参与';
            } elseif ($endDate > $time) {
                $item['supplier_biz_status'] = 'A';
                $item['biz_status_name'] = '待报价';
            } else {
                $item['supplier_biz_status'] = 'D';
                $item['biz_status_name'] = '未参与';
            }
            $item['entry_turns_name'] = $inquiryRepo->getTurnsText($item['entry_turns']);
            $item['entry_count_name'] = !empty($item['entry_count']) ? $item['entry_count'] : null;
        }
        (new SupplierBaseRepo)->setSuppliers($data);
        return $data;
    }

    public function getList(int $inquiryId, $bizStatusName = null, $bizStatus = null) {
        if (empty($inquiryId)) {
            return [];
        }

        $table = $this->model->getTable();
        $quote = (new Quote)->getTable();
        $inquiry = Inquiry::where('id', $inquiryId)
                ->selectRaw('end_date,turns')
                ->where('deleted_flag', 'N')
                ->first();
        if (empty($inquiry)) {
            check(false, '询价单不存在');
        }
        $endDate = $inquiry->end_date;
        $turns = $inquiry->turns;
        $supplierQuery = Supplier::from($table . ' as ss')
                ->selectRaw('ss.inquiry_id,ss.supplier_id,max(ss.entry_turns) as max_turns')
                ->orderBy('ss.entry_turns', 'desc')
                ->groupBy('ss.inquiry_id')
                ->groupBy('ss.supplier_id');
        $quoteQuery = Quote::from($quote . ' as q')
                ->selectRaw('q.inquiry_id,q.supplier_id,max(bill_date) AS max_bill_date')
                ->where('q.bill_status', 'C')
                ->where('q.deleted_flag', 'N')
                ->groupBy('q.inquiry_id')
                ->groupBy('q.supplier_id')
                ->orderBy('q.bill_date', 'desc');

        $qurey = $this->model->selectRaw('inquiry_supplier.supplier_id,inquiry_supplier.contact_name,'
                . 'inquiry_supplier.contact_phone,inquiry_supplier.contact_email,'
                . 'inquiry_supplier.entry_turns,entry_status,dead_line,'
                . 'supplier_biz_status,entry_count,q.bill_date AS quote_date,q.sum_tax_amount,q.id AS quote_id');
        $qurey->where('inquiry_supplier.inquiry_id', $inquiryId);
        $qurey->where('inquiry_supplier.deleted_flag', 'N');

        $object = $qurey
                ->joinSub($supplierQuery, 'max', function ($join) {
                    $join->on('inquiry_supplier.inquiry_id', '=', 'max.inquiry_id')
                    ->on('inquiry_supplier.entry_turns', '=', 'max.max_turns')
                    ->on('inquiry_supplier.supplier_id', '=', 'max.supplier_id');
                })
                ->leftJoinSub($quoteQuery, 'lastquote', function ($join) {
                    $join->on('lastquote.inquiry_id', '=', 'inquiry_supplier.inquiry_id')
                    ->on('lastquote.supplier_id', '=', 'inquiry_supplier.supplier_id');
                })
                ->leftJoin($quote . ' as q', function ($join) {
                    $join->on('q.inquiry_id', '=', 'inquiry_supplier.inquiry_id')
                    ->on('q.supplier_id', '=', 'inquiry_supplier.supplier_id')
                    ->on('q.bill_date', '=', 'lastquote.max_bill_date');
                })
                ->orderBy('inquiry_supplier.entry_turns', 'DESC')
                ->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $inquiryRepo = new InquiryRepo();

        $time = date('Y-m-d H:i:s');
        foreach ($data as &$item) {
            $item['entry_status'] = $bizStatus;
            $item['entry_status_name'] = $bizStatusName;
            $item['sum_tax_amount'] = !empty($item['sum_tax_amount']) ? number_format($item['sum_tax_amount'], 2, '.', ',') : null;
            if ($item['supplier_biz_status'] === 'E') {
                $item['supplier_biz_status'] = 'E';
                $item['supplier_entry_status_name'] = '已终止';
            } elseif (!empty($item['quote_id'])) {
                $item['supplier_biz_status'] = 'B';
                $item['biz_status_name'] = '已报价';
            } elseif ($endDate > $time && $turns > $item['entry_turns']) {
                $item['supplier_biz_status'] = 'D';
                $item['biz_status_name'] = '未参与';
            } elseif ($endDate > $time) {
                $item['supplier_biz_status'] = 'A';
                $item['biz_status_name'] = '待报价';
            } else {
                $item['supplier_biz_status'] = 'D';
                $item['biz_status_name'] = '未参与';
            }
            $item['entry_turns_name'] = $inquiryRepo->getTurnsText($item['entry_turns']);
            $item['entry_count_name'] = !empty($item['entry_count']) ? $item['entry_count'] : null;
        }
        (new SupplierBaseRepo)->setSuppliers($data);
        return $data;
    }

    public function updateData(int $inquiryId, Request $request) {
        Supplier::where('inquiry_id', $inquiryId)->delete();
        $attachList = $this->getSuppliers($inquiryId, $request);
        if (!empty($attachList)) {
            Supplier::insert($attachList);
        }
    }

    public function getSuppliers(int $inquiryId, Request $request) {
        $supplierList = [];
        $deadLine = !empty($request->base['end_date']) ? $request->base['end_date'] : null;
        if (!empty($request->suppliers)) {
            foreach ($request->suppliers as $supplier) {
                if (empty($supplier['supplier_id'])) {
                    continue;
                }
                $contact = (new SupplierContactRepo)->getDefaultContact($supplier['supplier_id']);
                $supplierList[] = [
                    'inquiry_id' => $inquiryId,
                    'seq' => !empty($supplier['seq']) ? $supplier['seq'] : '0',
                    'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : '0',
                    'quoter_id' => 0,
                    'quote_date' => null,
                    'entry_status' => '',
                    'supplier_biz_status' => 'A',
                    'entry_turns' => '1',
                    'entry_count' => 1,
                    'can_show' => !empty($supplier['can_show']) ? $supplier['can_show'] : 1,
                    'dead_line' => $deadLine,
                    'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
                    'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
                    'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
                    'created_at' => date('Y-m-d H:i:s'),
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
        }
    }

    public function getStatusText($status) {
        switch (strtoupper($status)) {
            case 'F':
                return '已中标';
            case 'H':
                return '报名截止';
            case 'K':
                return '保证金未收';
            case 'N':
                return '未报名';
            case 'T':
                return '待报名';
            case 'WCY':
                return '未参与';
            case 'Y':
                return '已报名';
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

    public function setSuppliers(&$list, &$maxSupplier = 0) {
        if (empty($list)) {
            return;
        }
        $inquiryIds = [];
        foreach ($list as &$inquiry) {
            $inquiry['suppliers'] = [];
            $inquiryIds[] = $inquiry['id'];
        }
        if (empty($inquiryIds)) {
            return;
        }
        $entryObj = $this->model
                ->selectRaw('inquiry_id,supplier_id'
                )
                ->whereIn('inquiry_id', $inquiryIds)
                ->where('deleted_flag', 'N')
                ->groupBy('inquiry_id')
                ->groupBy('supplier_id')
                ->get();

        if (empty($entryObj)) {
            return;
        }
        $supplierList = $entryObj->toArray();

        (new SupplierBaseRepo)->setSuppliers($supplierList);
        $supplierArr = [];
        foreach ($supplierList as $supplier) {
            $supplierArr[$supplier['inquiry_id']][] = $supplier;
        }

        foreach ($list as &$inquiry) {
            if (empty($supplierArr[$inquiry['id']])) {
                continue;
            }
            $maxSupplier = $maxSupplier > count($supplierArr[$inquiry['id']]) ? $maxSupplier : count($supplierArr[$inquiry['id']]);
            $inquiry['suppliers'] = $supplierArr[$inquiry['id']];
        }
    }

    public function updateCompareBizStatus(Request $request) {
        if (!empty($request->base['bill_status']) && $request->base['bill_status'] !== 'C') {
            return;
        }
        foreach ($request->quotes as $quote) {
            Supplier::where('supplier_id', $quote['supplier_id'])
                    ->where('quote_id', $quote['quote_id'])
                    ->update(['entry_status' => 'C',]);
        }
    }

}
