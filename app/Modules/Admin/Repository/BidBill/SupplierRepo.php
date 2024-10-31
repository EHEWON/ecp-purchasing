<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\BidBillSupplier;
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;

class SupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBillSupplier();
        parent::__construct($this->model);
    }

    public function getList(int $bidBillId, int $supplierId = null, $status = null) {
        if (empty($bidBillId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('bid_bill_id', $bidBillId);
        if (!empty($supplierId)) {
            $qurey->where('supplier_id', $supplierId);
        }
        if (!empty($status)) {
            $qurey->where('entry_status', $status);
        }
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($data);
        foreach ($data as &$item) {
            $item['entry_status_name'] = $this->getEntryStatusText($item['entry_status']);
        }
        (new UserRepo)->setUsers($data, 'audit_id', 'audit_name');
        (new UserRepo)->setUsers($data, 'pay_id', 'pay_name');
        (new UserRepo)->setUsers($data, 'enroll_id', 'enroll_name');
        (new UserRepo)->setUsers($data, 'return_id', 'return_name');
        return $data;
    }

    public function updateData(int $bidBillId, Request $request) {
        BidBillSupplier::where('bid_bill_id', $bidBillId)->delete();
        $attachList = $this->getSuppliers($bidBillId, $request);
        if (!empty($attachList)) {
            BidBillSupplier::insert($attachList);
        }
    }

    public function getSuppliers(int $bidBillId, Request $request) {
        $supplierList = [];
        if (!empty($request->suppliers)) {
            foreach ($request->suppliers as $key => $supplier) {
                $supplierList[] = [
                    'bid_bill_id' => $bidBillId,
                    'seq' => $key + 1,
                    'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : null,
                    'enroll_date' => !empty($supplier['enroll_date']) ? $supplier['enroll_date'] : null,
                    'audit_date' => !empty($supplier['audit_date']) ? $supplier['audit_date'] : null,
                    'pay_date' => !empty($supplier['pay_date']) ? $supplier['pay_date'] : null,
                    'return_date' => !empty($supplier['return_date']) ? $supplier['return_date'] : null,
                    'amount' => !empty($supplier['amount']) ? $supplier['amount'] : null,
                    'ranking' => !empty($supplier['ranking']) ? $supplier['ranking'] : null,
                    'note' => !empty($supplier['note']) ? $supplier['note'] : null,
                    'entry_status' => !empty($supplier['entry_status']) ? $supplier['entry_status'] : 'T',
                    'enroll_id' => !empty($supplier['enroll_id']) ? $supplier['enroll_id'] : null,
                    'audit_id' => !empty($supplier['audit_id']) ? $supplier['audit_id'] : null,
                    'pay_id' => !empty($supplier['pay_id']) ? $supplier['pay_id'] : null,
                    'return_id' => !empty($supplier['return_id']) ? $supplier['return_id'] : null,
                    'allow_bid' => !empty($supplier['allow_bid']) ? $supplier['allow_bid'] : '0',
                    'remark' => !empty($supplier['remark']) ? $supplier['remark'] : null,
                    'result' => !empty($supplier['fk_erui_entry_result']) ? $supplier['fk_erui_entry_result'] : null,
                ];
            }
        }
        return $supplierList;
    }

    public function getEntryStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':return '待资审';
            case 'B':return '资审通过';
            case 'C':return '资审未通过';
            case 'D':return '保证金已审';
            case 'E':return '保证金已退';
            case 'F':return '已中标';
            case 'G':return '未中标';
            case 'H':return '报名截止';
            case 'J':return '未竞价';
            case 'K':return '保证金未收';
            case 'L':return '待缴费';
            case 'WQR':return '已缴费未确认';
            case 'M':return '竞价中';
            case 'N':return '未报名';
            case 'O':return '已缴费';
            case 'P':return '已暂停';
            case 'Q':return '评标中';
            case 'T':return '待报名';
            case 'WCY':return '未参与';
            case 'Y':return '已报名';
            case 'S':return '终止';
        }
    }

    public function setQuoteNums(&$list) {
        if (empty($list)) {
            return;
        }
        $bidBillIds = [];
        foreach ($list as &$item) {
            $bidBillIds[] = $item['id'];
            $item['quote_num'] = 0;
            $item['check_num'] = 0;
            $item['deposit_num'] = 0;
            $item['allow_num'] = 0;
        }
        $supplierObj = BidBillSupplier::selectRaw('count(id) AS quote_num,'
                        . 'bid_bill_id,entry_status,sum(if(allow_bid=1,1,0)) AS allow_num')
                ->whereIn('bid_bill_id', $bidBillIds)
                ->whereNotIn('entry_status', ['T', 'WCY', 'N'])
                ->groupBy('bid_bill_id')
                ->groupBy('entry_status')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        $supplierArr = [];
        $supplierAllowArr = [];
        foreach ($supplierList as $supplier) {
            $supplierArr[$supplier['bid_bill_id']][$supplier['entry_status']] = $supplier['quote_num'];
            if (isset($supplierAllowArr[$supplier['bid_bill_id']])) {
                $supplierAllowArr[$supplier['bid_bill_id']] += $supplier['allow_num'];
            } else {
                $supplierAllowArr[$supplier['bid_bill_id']] = $supplier['allow_num'];
            }
        }
        foreach ($list as &$val) {
            if (isset($supplierAllowArr[$val['id']])) {
                $val['allow_num'] += $supplierAllowArr[$val['id']];
            }
            if (isset($supplierArr[$val['id']])) {
                $suppliers = $supplierArr[$val['id']];
                foreach ($suppliers as $entryStatus => $quoteNum) {
                    $val['quote_num'] += $quoteNum;
                    switch ($entryStatus) {
                        case 'B':
                        case 'K':
                        case 'L':
                            $val['check_num'] += $quoteNum;
                            break;
                        case 'D':
                        case 'E':
                        case 'F':
                        case 'G':
                        case 'J':
                            $val['deposit_num'] += $quoteNum;
                            break;
                    }
                }
            }
        }
    }

    public function setQuoteNum(&$item) {
        if (empty($item)) {
            return;
        }

        $bidBillIds = [];

        $bidBillIds[] = $item['id'];
        $item['quote_num'] = 0;
        $item['check_num'] = 0;
        $item['allow_num'] = 0;
        $supplierObj = BidBillSupplier::selectRaw('count(id) AS quote_num,'
                        . 'bid_bill_id,entry_status,sum(if(allow_bid=1,1,0)) AS allow_num')
                ->whereIn('bid_bill_id', $bidBillIds)
                ->whereNotIn('entry_status', ['T', 'WCY', 'N'])
                ->groupBy('bid_bill_id')
                ->groupBy('entry_status')
                ->get();
        if (empty($supplierObj)) {
            return;
        }


        $suppliers = $supplierObj->toArray();
        foreach ($suppliers as $entryStatus => $supplier) {
            $item['allow_num'] += $supplier['allow_num'];
            $item['quote_num'] += $supplier['quote_num'];
            switch ($entryStatus) {
                case 'B':
                case 'K':
                case 'L':
                    $item['check_num'] += $supplier['quote_num'];
                    break;
                case 'D':
                case 'E':
                case 'F':
                case 'G':
                case 'J':
                    $item['deposit_num'] += $supplier['quote_num'];
                    break;
            }
        }
    }

}
