<?php

namespace App\Modules\Admin\Repository\Quote;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Compare\Compare,
    Compare\CompareQuote,
    Inquiry\Inquiry,
    Purchaser,
    Quote\Quote
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    Inquiry\InquiryRepo,
    PaycondRepo,
    Quote\AttachRepo,
    Quote\EntryRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\{
    Style\Alignment,
    Style\Border,
    Style\Font
};

class QuoteRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'inquiry_title',
        'biz_status',
        'bill_status',
        'bill_date',
        'end_date',
        'org_id',
    ];

    public function __construct() {
        $this->model = new Quote();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'q.bill_date';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'bill_date') {
            $query->orderBy('i.bill_date', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'q.id,q.bill_no,q.org_id,'
    . 'q.inquiry_title,q.bill_date,q.sum_tax_amount,q.biz_status,q.updated_at,q.created_at,'
    . 'q.bill_status,q.curr_id,q.supplier_id,q.loc_curr_id,q.inquiry_no,q.inquiry_id') {

        $quote = $this->model->getTable();
        $inquiry = (new Inquiry)->getTable();
        $query = $this->model
                ->from($quote . ' AS q')
                ->join($inquiry . ' AS i', function($join) {
                    $join->on('i.id', '=', 'q.inquiry_id')
                    ->where('i.bill_status', 'C')
                    ->whereIn('i.biz_status', ['B', 'C', 'D', 'E']);
                })
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $query->where('q.bill_status', 'C');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['sum_tax_amount'] = number_format($item['sum_tax_amount'], 2, '.', ',');
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            list($item['status_name'], $item['status']) = $this->getStatusText($item['bill_status'], $item['biz_status']);
        }
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_number' => 'number', 'curr_sign' => 'sign']);
        (new CurrencyRepo)->setCurrencys($data, 'loc_curr_id', ['loc_curr_number' => 'number', 'loc_curr_sign' => 'sign']);
        (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }


    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $query = $this->model->selectRaw('id,bill_no,inquiry_title,bill_date,deli_date,deli_addr,'
                . 'org_id,person_id,settle_type_id,curr_id,person_id,contact_name,contact_phone,contact_email,'
                . 'loc_curr_id,inv_type,end_date,date_from,date_to,bill_status,sum_tax_amount,other_pay_terms_info,'
                . 'payment_terms,settlement_method,total_inquiry,remark,biz_status,date_from,date_to,'
                . 'tax_cal_type,turns,turns_count,delivery_date,inv_type,inquiry_no,inquiry_id,supplier_id,created_at,updated_at');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = [];
        $base = $object->toArray();
        $base['bill_status_name'] = $this->getBillStatusText($base['bill_status']);
        $base['biz_status_name'] = $this->getBizStatusText($base['biz_status']);
        (new UserRepo)->setUser($base, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrency($base, 'curr_id', 'curr_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settle_type_id', 'settle_type_name');
        (new SettleMentTypeRepo)->setSettleMentType($base, 'settlement_method', 'settlement_method_name');
        (new PaycondRepo)->setPaycond($base, 'payment_terms', ['payment_terms_name' => 'name']);
        $base['tax_cal_type_name'] = (new InquiryRepo)->getTaxCalTypeText($base['tax_cal_type']);
        $base['inv_type_name'] = (new InquiryRepo)->getInvtypeText($base['inv_type']);
        (new SupplierBaseRepo)->setSupplier($base, 'supplier_id', 'supplier_name');
        list($base['status_name'], $base['status']) = $this->getStatusText($base['bill_status'], $base['biz_status']);
        $base['turns_name'] = (new InquiryRepo)->getTurnsText($base['turns']);
        $data['base'] = $base;
        $data['attachs'] = (new AttachRepo)->getList($id);
        $data['entrys'] = (new EntryRepo)->getList($id);

//        $data['suppliers'] = (new SupplierRepo)->getList($id);
        return $data;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    public function getWhere(&$query, Request $request) {
        $Supplier = (new \App\Common\Models\Supplier)->getTable();
        $purchaser = (new Purchaser)->getTable();
        $query->where('q.deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword, $Supplier) {
                $q->where('q.inquiry_title', 'like', '%' . $keyword . '%')
                        ->orWhere('q.bill_no', 'like', '%' . $keyword . '%')
                        ->orWhere('q.inquiry_no', 'like', '%' . $keyword . '%')
                        ->orWhereRaw('EXISTS(SELECT p.id FROM ' . $Supplier
                                . ' as p WHERE p.name like \'%' . $keyword . '%\''
                                . ' AND p.deleted_flag=\'N\' AND p.id=q.supplier_id)');
            });
        }

        if (!empty($request->bill_no)) {
            $query->where('q.bill_no', 'like', '%' . trim($request->bill_no) . '%');
        }
        if (!empty($request->inquiry_id)) {
            $query->where('q.inquiry_id', trim($request->inquiry_id));
        }
        if (!empty($request->inquiry_title)) {
            $query->where('q.inquiry_title', 'like', '%' . trim($request->inquiry_title) . '%');
        }
        if (!empty($request->related_no)) {
            $query->where('i.related_no', 'like', '%' . trim($request->related_no) . '%');
        }
        if (!empty($request->inquiry_no)) {
            $query->where('q.inquiry_no', 'like', '%' . trim($request->inquiry_no) . '%');
        }
        if (!empty($request->bill_status)) {
            $query->where('q.bill_status', $request->bill_status);
        }
        if (!empty($request->biz_status)) {
            $query->where('q.biz_status', $request->biz_status);
        }
        if (!empty($request->status)) {
            switch (strtoupper($request->status)) {
                case 'A':
                    $query->where('q.bill_status', 'A');
                    break;
                case 'B':
                    $query->where('q.bill_status', 'B');
                    break;
                case 'CA':
                    $query->where('q.bill_status', 'C');
                    $query->where('q.biz_status', 'A');
                    break;

                case 'CB':
                    $query->where('q.bill_status', 'C');
                    $query->where('q.biz_status', 'B');
                    break;

                case 'CD':
                    $query->where('q.bill_status', 'C');
                    $query->where('q.biz_status', 'D');
                    break;
                case 'CE':
                    $query->where('q.bill_status', 'C');
                    $query->where('q.biz_status', 'E');
                    break;

                case 'D':
                    $query->where('q.bill_status', 'D');
                    break;
                case 'Z':
                    $query->where('q.bill_status', 'E');
                    break;
            }
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('q.bill_date', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('q.bill_date', $createAts);
        }
    }

    public function getBillStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '待报价';
            case 'B':
                return '报价中';
            case 'C':
                return '已报价';
            case 'D':
                return '已关闭';
            case 'Z':
                return '已作废';
        }
    }

    public function getStatusText($status, $bizStatus) {
        switch (strtoupper($status)) {
            case 'A':
                return ['待报价', 'A'];
            case 'B':
                return ['报价中', 'B'];
            case 'C':
                switch (strtoupper($bizStatus)) {
                    case 'A':
                        return ['已报价', 'AA'];
                    case 'B':
                        return ['已开标', 'AB'];
                    case 'C':
                        return ['已采纳', 'AC'];
                    case 'D':
                        return ['部分采纳', 'AD'];
                    case 'E':
                        return ['未采纳', 'AE'];
                }
            case 'D':
                return ['已关闭', 'D'];
            case 'Z':
                return ['已作废', 'Z'];
        }
    }

    public function getBizStatusText($status) {
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
        }
    }

    public function getBizStatusList() {
        return [
            'A' => '已报价',
            'B' => '已开标',
            'C' => '已采纳',
            'D' => '部分采纳',
            'E' => '未采纳',
        ];
    }

    public function getBillStatusList() {
        return [
            'A' => '待报价',
            'B' => '报价中',
            'C' => '已报价',
            'D' => '已关闭',
            'Z' => '已作废',
        ];
    }

    public function getStatusList() {
        return [
            'A' => '待报价',
            'B' => '报价中',
            'CA' => '已报价',
            'CB' => '已开标',
            'CC' => '已采纳',
            'CD' => '部分采纳',
            'CE' => '未采纳',
            'D' => '已关闭',
            'Z' => '已作废',
        ];
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getQuoteNo() {
        $prefix = 'BJ';
        $qurey = $this->model->selectRaw('*');
        $supplierNo = $qurey
                ->where('supplier_no', 'like', $prefix . '%')
                ->orderBy('supplier_no', 'DESC')
                ->value('supplier_no');
        if (!empty($supplierNo)) {
            $date = substr($supplierNo, 2, 8);
            $serialSetp = substr($supplierNo, 10, 5);
            $step = intval($serialSetp);
            $step ++;
            return $this->createSerialNo($step, $prefix, $date);
        }
        return$this->createSerialNo(1, $prefix, '');
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2019-03-11
     * @return string $code
     */
    private function createSerialNo($step = 1, $prefix = '', $date = '') {
        $time = date('Ymd');
        if (empty($date) || $date < $time) {
            $step = 1;
        }
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        return$prefix . $time . $pad;
    }

    public function sumQuote(int $inquiryId) {
        $filed = 'q.id,q.bill_no,q.org_id,q.warranty_period,q.payment_terms,q.other_pay_terms_info,'
                . 'q.inquiry_title,q.bill_date,q.sum_tax_amount,q.biz_status,q.tax_cal_type,q.delivery_date,'
                . 'q.bill_status,q.curr_id,q.supplier_id,q.loc_curr_id,q.inquiry_no,q.sum_cost,q.sum_amount,'
                . 'cq.adopt_total_amount';
        $table = $this->model->getTable();
        $subTable = (new CompareQuote)->getTable();
        $inquiry = Inquiry::where('id', $inquiryId)
                        ->selectRaw('total_inquiry,biz_status,turns,other_pay_terms_info,payment_terms'
                        )->first();
        $compareId = Compare::where('inquiry_id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->value('id');
        check(!empty($inquiry), '询价单不存在');
        $totalInquiry = $inquiry->total_inquiry;
        $quoteQuery = Quote::from($table . ' as sq')
                ->selectRaw('sq.inquiry_id,sq.supplier_id,max(sq.turns) as max_turns,max(sq.id) AS max_quote_id')
                ->where('sq.bill_status', 'C')
                ->where('sq.deleted_flag', 'N')
                ->orderBy('sq.turns', 'DESC')
                ->groupBy('sq.inquiry_id')
                ->groupBy('sq.supplier_id');
        $bizStatus = $inquiry->biz_status;
        $query = $this->model
                ->from($table . ' as q')
                ->joinSub($quoteQuery, 'max', function ($join) {
                    $join->on('q.inquiry_id', '=', 'max.inquiry_id')
                    ->on('q.turns', '=', 'max.max_turns')
                    ->on('q.id', '=', 'max.max_quote_id')
                    ->on('q.supplier_id', '=', 'max.supplier_id');
                })
                ->leftJoin($subTable . ' as cq', function($join) use($compareId) {
                    $join->on('q.id', '=', 'cq.quote_id');
                    $join->where('cq.compare_id', !empty($compareId) ? $compareId : -1);
                })
                ->selectRaw($filed);
        $query->where('q.bill_status', 'C');
        $query->where('q.inquiry_id', $inquiryId);
        $query->orderBy('q.bill_date', 'ASC')
                ->orderBy('q.supplier_id', 'ASC');
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        if (empty($data)) {
            return [];
        }

        $inquiryRepo = new \App\Modules\Admin\Repository\Inquiry\InquiryRepo();
        $minSumAmount = min(array_column($data, 'sum_tax_amount'));
        list($entrys, $supplierIds, $sumAmount) = (new EntryRepo)->details($inquiryId, $inquiry);
        $supplierId = null;
        $acceptFlag = false;

        foreach ($data as &$item) {
            $item['payment_terms'] = $item['payment_terms'];
            $item['other_pay_terms_info'] = $item['other_pay_terms_info'];
            $item['sum_tax_amount'] = number_format($item['sum_tax_amount'], 2, '.', '');
            $item['sum_cost'] = number_format($item['sum_cost'], 2, '.', '');
            $item['sum_amount'] = number_format($item['sum_amount'], 2, '.', '');
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            $item['tax_cal_type_name'] = $inquiryRepo->getTaxCalTypeText($item['tax_cal_type']);
            $qbizStatus = $item['biz_status'];
            if ($qbizStatus === 'C' || $qbizStatus === 'D') {
                $item['adopt_flag'] = true;
                $item['adopt_total_amount'] = !empty($item['adopt_total_amount']) ? $item['adopt_total_amount'] : $item['sum_tax_amount'];
                continue;
            } elseif ($qbizStatus === 'E') {
                $item['adopt_flag'] = false;
                $item['adopt_total_amount'] = null;
                continue;
            }
            if ($bizStatus !== 'B') {
                $item['adopt_flag'] = false;
                $item['adopt_total_amount'] = null;
                continue;
            }
            if ($totalInquiry == '1' && $item['sum_tax_amount'] === number_format($minSumAmount, 2, '.', '') && $acceptFlag === false) {
                $item['adopt_flag'] = true;
                $item['adopt_total_amount'] = number_format($minSumAmount, 2, '.', '');
                $supplierId = $item['supplier_id'];
                $acceptFlag = true;
                continue;
            } elseif ($totalInquiry != '1' && in_array($item['supplier_id'], $supplierIds)) {
                $item['adopt_flag'] = true;
                $item['adopt_total_amount'] = !empty($sumAmount[$item['supplier_id']]) ? $sumAmount[$item['supplier_id']] : null;
                continue;
            }
            $item['adopt_flag'] = false;
            $item['adopt_total_amount'] = null;
        }
        (new PaycondRepo)->setPayconds($data, 'payment_terms', $fieldKeys = ['payment_terms_name' => 'name', 'payment_terms_number' => 'number']);
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_number' => 'number', 'curr_sign' => 'sign']);
        (new CurrencyRepo)->setCurrencys($data, 'loc_curr_id', ['loc_curr_number' => 'number', 'loc_curr_sign' => 'sign']);
        (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');
        if ($totalInquiry !== '1') {
            return ['data' => $data, 'entrys' => $entrys];
        }
        foreach ($entrys as &$entryList) {
            $quotes = $entryList['quotes'];
            foreach ($quotes as &$quote) {
                if ($bizStatus === 'B' && !empty($supplierId) && $supplierId == $quote['supplier_id']) {
                    $quote['adopt_flag'] = true;
                    $quote['adopt_total_amount'] = $quote['cfm_tax_amount'];
                    continue;
                }
                $quote['adopt_flag'] = false;
                $quote['accept_amount'] = null;
            }
            $entryList['quotes'] = $quotes;
        }
        return ['data' => $data, 'entrys' => $entrys];
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $filed = 'id,bill_no,org_id,inquiry_id,turns_count,delivery_date,date_from,date_to,'
                . 'inquiry_title,bill_date,sum_tax_amount,biz_status,end_date,'
                . 'bill_status,curr_id,supplier_id,tax_cal_type,contact_name,contact_phone,contact_email,'
                . 'inquiry_no,settlement_method,payment_terms,inv_type';
        $query = $this->model->selectRaw($filed);
        $query->where('bill_status', 'C');
        if ($request->type === 'ALL') {
            $query->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('deleted_flag', 'N')
                    ->whereIn('id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
//        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['bill_status_name'] = $this->getBillStatusText($item['bill_status']);
            $item['biz_status_name'] = $this->getBizStatusText($item['biz_status']);
            $item['tax_cal_type_name'] = (new InquiryRepo)->getTaxCalTypeText($item['tax_cal_type']);
            $item['inv_type_name'] = (new InquiryRepo)->getInvtypeText($item['inv_type']);
        }
//        (new InquiryRepo)->setInquirys($data);
        (new SettleMentTypeRepo)->setSettleMentTypes($data, 'settlement_method', 'settlement_method_name');
        (new PaycondRepo)->setPayconds($data, 'payment_terms', ['payment_terms_name' => 'name']);
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new CurrencyRepo)->setCurrencys($data, 'curr_id', ['curr_name' => 'name']);
        (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');
        (new UserRepo)->setUsers($data, 'person_id', 'person_name');
        (new EntryRepo)->setEntrys($data);

        $headName = $this->getHeadName();
        $xlsName = '报价单_' . date("YmdHis", time()) . uniqid(); //文件名称
        return $this->downloadExcel($xlsName, $data, $headName);
    }

    private $styleArray = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
        'font' => [
            'name' => 'Arial',
            'bold' => false,
            'italic' => false,
            'size' => 9,
            'underline' => Font::UNDERLINE_NONE,
            'strikethrough' => false,
            'color' => [
                'rgb' => '000000'
            ]
        ],
        'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '00000000'],
            ],
        ],
    ];

    public function setExcelRow($sheet, $col, $row, $value, $width) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->applyFromArray($this->styleArray);
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    /**
     * 导出
     * @param type $request Description
     * @param $name
     * @param array $data
     * @param array $head
     * @return array
     */
    public function downloadExcel($name, $data = [], $head = []) {
        $count = count($head);  //计算表头数量
        $spreadsheet = Excel::newSpreadsheet();
        $styleArray = $this->styleArray;
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        for ($i = 1; $i <= $count; $i++) {     //数字转字母从65开始
            $column = Excel::num2alpha($i);
            $this->setExcelRow($sheet, $column, 1, $head[$i - 1], 20);
        }
        $row = 2;
        foreach ($data as $item) {
            //数字转字母从65开始：
            if (empty($item['entrys'])) {
                $this->setQuoteExcelRow($item, $sheet, $row, $styleArray);
                $row++;
                continue;
            }
            foreach ($item['entrys'] as $entry) {
                $this->setQuoteExcelRow($item, $sheet, $row, $styleArray);
                $this->setExcelRow($sheet, 'T', $row, $entry['material_name'], 24);
                $sheet->getStyle('T' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'U', $row, $entry['material_desc'], 24);
                $sheet->getStyle('U' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'V', $row, $entry['inquire_qty'], 24);
                $sheet->getStyle('V' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'W', $row, $entry['inquiry_unit_id_name'], 24);
                $sheet->getStyle('W' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'X', $row, $entry['qty'], 24);
                $sheet->getStyle('X' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'Y', $row, $entry['quote_unit_id_name'], 24);
                $sheet->getStyle('Y' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'Z', $row, number_format($entry['tax_rate'], 8, '.', ','), 24);
                $sheet->getStyle('Z' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AA', $row, number_format($entry['tax_price'], 4, '.', ','), 24);
                $sheet->getStyle('AA' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AB', $row, number_format($entry['price'], 4, '.', ','), 24);
                $sheet->getStyle('AB' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AC', $row, number_format($entry['tax'], 2, '.', ','), 24);
                $sheet->getStyle('AC' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AD', $row, number_format($entry['amount'], 2, '.', ','), 24);
                $sheet->getStyle('AD' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AE', $row, number_format($entry['tax_amount'], 2, '.', ','), 24);
                $sheet->getStyle('AE' . $row)->applyFromArray($styleArray);
                $this->setExcelRow($sheet, 'AF', $row, $entry['warranty_period'], 24);
                $sheet->getStyle('AF' . $row)->applyFromArray($styleArray);
                $row++;
                continue;
            }
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:G2')
                ->applyFromArray($styleArray);
        $realtive = "/download/" . date("Ymd") . '/';
        $filename = $name . '.xlsx';
        $filedir = base_path() . '/public' . $realtive;
        @mkdir($filedir, 0777, true);
        $filepath = $filedir . $filename;
        $spreadsheet->save($filepath);
        $url = env('APP_URL') . $realtive . $filename;
        return ['file_url' => $url, 'attach_name' => $filename];
    }

    public function setQuoteExcelRow($item, $sheet, $row, $styleArray) {
        $this->setExcelRow($sheet, 'A', $row, ' ' . $item['bill_no'], 17);
        $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'B', $row, $item['biz_status_name'], 24);
        $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'C', $row, $item['supplier_name'], 24);
        $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'E', $row, $item['inquiry_title'], 24);
        $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'F', $row, $item['bill_date'], 24);
        $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'G', $row, $item['end_date'], 24);
        $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'H', $row, $item['inquiry_no'], 24);
        $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'I', $row, $item['turns_count'], 24);
        $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'J', $row, $item['delivery_date'], 24);
        $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'K', $row, $item['contact_name'], 24);
        $sheet->getStyle('K' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'L', $row, $item['contact_phone'] . '/' . $item['contact_email'], 24);
        $sheet->getStyle('L' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'M', $row, $item['date_from'], 24);
        $sheet->getStyle('M' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'N', $row, $item['date_to'], 24);
        $sheet->getStyle('N' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'O', $row, $item['settlement_method_name'], 24);
        $sheet->getStyle('O' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'P', $row, $item['payment_terms_name'], 24);
        $sheet->getStyle('P' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'Q', $row, $item['tax_cal_type_name'], 24);
        $sheet->getStyle('Q' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'R', $row, $item['curr_name'], 24);
        $sheet->getStyle('R' . $row)->applyFromArray($styleArray);
        $this->setExcelRow($sheet, 'S', $row, $item['inv_type_name'], 24);
        $sheet->getStyle('S' . $row)->applyFromArray($styleArray);
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '报价单号',
            '项目状态',
            '供应商',
            '采购组织',
            '询价标题',
            '报价日期',
            '报价截止日期',
            '询价单号',
            '轮次',
            '交货期描述',
            '报价联系人',
            '报价方联系方式',
            '价格有效期从',
            '价格有效期至',
            '结算方式',
            '付款条件',
            '计税类型',
            '币种',
            '发票类型',
            '物料名称',
            '物料描述',
            '询价数量',
            '询价单位',
            '报价数量',
            '报价单位',
            '税率%',
            '含税单价',
            '单价',
            '税额',
            '金额',
            '价税合计',
            '质保期（天）',
        ];
    }

}
