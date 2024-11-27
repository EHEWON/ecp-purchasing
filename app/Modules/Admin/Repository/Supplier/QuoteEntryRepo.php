<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Inquiry\Entry as InquiryEntry,
    Inquiry\Inquiry,
    Quote\Quote,
    Quote\QuoteEntry,
    TaxRate,
    Unit
};
use App\Modules\Admin\Repository\{
    Inquiry\EntryRepo as InquiryEntryRepo,
    TaxRateRepo,
    UnitRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};
use function RecursiveMkdir;

class QuoteEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new QuoteEntry();
        parent::__construct($this->model);
    }

    public function getList(int $quoteId) {
        if (empty($quoteId)) {
            return [];
        }
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.material_name,e.qty,e.deli_address,'
                . 'e.delive_method,e.deli_at,e.inquiry_entry_id,'
                . 'e.tax_rate,e.price,e.tax_price,e.amount,e.tax_amount,e.tax,'
                . 'e.pobill_no,e.pcbill_no,e.quote_curr,e.tax_rate_id,e.note,'
                . 'e.exrate,e.warranty_period,e.inquire_qty,e.inquiry_precision,'
                . 'e.price_field,e.boss_goods,e.spec_model,e.`precision`,e.stock_code,e.spec_model,e.specification_model,'
                . 'e.created_at,e.created_by,e.inquiry_unit_id,e.material_desc,e.quote_unit_id');
        $qurey->where('e.quote_id', $quoteId);
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');
        (new UnitRepo)->setUnits($list, 'quote_unit_id', 'quote_unit_name');
        (new TaxRateRepo)->setTaxRates($list, 'tax_rate_id', 'tax_rate_name');
        foreach ($list as &$item) {
            $item['tax_rate_id'] = !empty($item['tax_rate_id']) ? $item['tax_rate_id'] : null;
            $item['price'] = number_format($item['price'], 4, '.', '');
            $item['tax_price'] = number_format($item['tax_price'], 4, '.', '');
            $item['inquire_qty'] = number_format($item['inquire_qty'], $item['precision']);
            $item['tax_rate'] = number_format($item['tax_rate'], 2, '.', '');
            $item['amount'] = number_format($item['amount'], 2, '.', '');
            $item['tax'] = number_format($item['tax'], 2, '.', '');
            $item['tax_amount'] = number_format($item['tax_amount'], 2, '.', '');
            $item['qty'] = number_format($item['qty'], $item['precision'], '.', '');
        }
        return $list;
    }

    public function getDefaultEntrys($inquiryId) {
        $inquiryEntryObj = InquiryEntry::where('inquiry_id', $inquiryId)
                ->where('deleted_flag', 'N')
                ->get();
        if (empty($inquiryEntryObj)) {
            return [];
        }
        $inquiryEntryList = $inquiryEntryObj->toArray();
        $ret = [];
        foreach ($inquiryEntryList as $item) {

            $ret[] = [
                'material_name' => $item['material_name'],
                'qty' => null,
                'deli_address' => $item['deli_address'],
                'delive_method' => $item['delive_method'],
                'deli_at' => $item['deli_at'],
                'inquiry_entry_id' => $item['id'],
                'inquire_qty' => $item['inquire_qty'],
                'inquiry_precision' => $item['precision'],
                'tax_rate' => null,
                'price' => null,
                'tax_price' => null,
                'amount' => null,
                'pobill_no' => '',
                'pcbill_no' => '',
                'quote_curr' => $item['id'],
                'exrate' => null,
                'warranty_period' => $item['warranty_period'],
                'new_qty' => null,
                'price_field' => null,
                'spec_model' => '',
                'specification_model' => '',
                'boss_goods' => 0,
                'spec_model' => '',
                'precision' => 0,
                'created_at' => null,
                'created_by' => null,
                'inquiry_unit_id' => $item['inquiry_unit_id'],
                'quote_unit_id' => $item['inquiry_unit_id'],
                'material_desc' => $item['material_desc'],];
        }
        (new UnitRepo)->setUnits($ret, 'inquiry_unit_id', 'inquiry_unit_name');
        (new UnitRepo)->setUnits($ret, 'quote_unit_id', 'quote_unit_name');
        return $ret;
    }

    public function getPrice(&$price, &$taxPrice, $taxRate, $taxCalType) {

        switch ($taxCalType) {
            case '1'://价外税(含税)
                $price = round($taxPrice / (1 + $taxRate / 100), 8);
                return;
            case '3'://价内税(含税)
                $price = round($taxPrice * (1 - $taxRate / 100), 8);
                return;
            case '2'://价外税(不含税)
                $taxPrice = round($price * (1 + $taxRate / 100), 8);
                return;
        }
    }

    public function updateData(int $inquiryId, $quoteId, Request $request) {
        $admin = Auth::guard('admin')->user();
        QuoteEntry::where('quote_id', $quoteId)->delete();
        $inquiryEntryObj = InquiryEntry::where('inquiry_id', $inquiryId)->where('deleted_flag', 'N')->get();
        $taxCalType = Inquiry::where('id', $inquiryId)->value('tax_cal_type');
        $inquiryEntryArr = [];
        if (!empty($inquiryEntryObj)) {
            $inquiryEntryList = $inquiryEntryObj->toArray();
            foreach ($inquiryEntryList as $item) {
                $inquiryEntryArr[$item['id']] = $item;
            }
        }
        $sumAmount = $sumTax = $sumTaxAmount = $sumQty = 0;
        if (!empty($request->entrys)) {
            $entrys = $request->entrys;
            (new UnitRepo)->setUnitPrecisions($entrys, 'quote_unit_id');
            foreach ($entrys as $key => $entry) {
                $qty = !empty($entry['qty']) ? $entry['qty'] : 0;
                $price = !empty($entry['price']) ? $entry['price'] : 0;
                $taxPrice = !empty($entry['tax_price']) ? $entry['tax_price'] : 0;
                $taxRate = !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0;
                if ($price !== '' && $taxPrice !== '') {
                    $this->getPrice($price, $taxPrice, $taxRate, $taxCalType);
                    $sumQty += $qty;
                    $amount = round($qty * $price, 8);
                    $sumAmount += $amount;
                    $taxAmount = round($qty * $taxPrice, 8);
                    $sumTaxAmount += $taxAmount;
                    $tax = round($qty * ($taxPrice - $price), 8);
                    $sumTax += $tax;
                } else {
                    $price = null;
                    $taxPrice = null;
                    $amount = null;
                    $taxAmount = null;
                    $tax = null;
                }

                $inquiryEntry = !empty($inquiryEntryArr[$entry['inquiry_entry_id']]) ? $inquiryEntryArr[$entry['inquiry_entry_id']] : [];
                $entryData = [
                    'seq' => $key + 1,
                    'inquiry_id' => $inquiryId,
                    'material_id' => !empty($inquiryEntry['material_id']) ? $inquiryEntry['material_id'] : 0,
                    'material_desc' => !empty($inquiryEntry['material_desc']) ? $inquiryEntry['material_desc'] : null,
                    'unit_id' => !empty($inquiryEntry['unit_id']) ? $inquiryEntry['unit_id'] : 0,
                    'deli_date' => !empty($inquiryEntry['deli_date']) ? $inquiryEntry['deli_date'] : null,
                    'deli_addr' => !empty($inquiryEntry['deli_addr']) ? $inquiryEntry['deli_addr'] : null,
                    'deli_type_id' => !empty($inquiryEntry['deli_type_id']) ? $inquiryEntry['deli_type_id'] : 0,
                    'dct_rate' => !empty($inquiryEntry['dct_rate']) ? $inquiryEntry['dct_rate'] : 0,
                    'dct_amount' => !empty($inquiryEntry['dct_amount']) ? $inquiryEntry['dct_amount'] : 0,
                    'tax' => $tax,
                    'tax_amount' => $taxAmount,
                    'req_org_id' => !empty($inquiryEntry['req_org_id']) ? $inquiryEntry['req_org_id'] : 0,
                    'pur_org_id' => !empty($inquiryEntry['pur_org_id']) ? $inquiryEntry['pur_org_id'] : 0,
                    'rcv_org_id' => !empty($inquiryEntry['rcv_org_id']) ? $inquiryEntry['rcv_org_id'] : 0,
                    'settle_org_id' => !empty($inquiryEntry['settle_org_id']) ? $inquiryEntry['settle_org_id'] : 0,
                    'pay_org_id' => !empty($inquiryEntry['pay_org_id']) ? $inquiryEntry['pay_org_id'] : 0,
                    'note' => !empty($entry['note']) ? $entry['note'] : null,
                    'entry_status' => !empty($inquiryEntry['entry_status']) ? $inquiryEntry['entry_status'] : null,
                    'project_id' => !empty($inquiryEntry['project_id']) ? $inquiryEntry['project_id'] : 0,
                    'trace_id' => !empty($inquiryEntry['trace_id']) ? $inquiryEntry['trace_id'] : 0,
                    'inquiry_unit_id' => !empty($inquiryEntry['inquiry_unit_id']) ? $inquiryEntry['inquiry_unit_id'] : 0,
                    'specification_model' => !empty($inquiryEntry['specification_model']) ? $inquiryEntry['specification_model'] : '',
                    'inquire_qty' => !empty($inquiryEntry['inquire_qty']) ? $inquiryEntry['inquire_qty'] : 0,
                    'inquiry_precision' => !empty($inquiryEntry['precision']) ? $inquiryEntry['precision'] : 0,
                    'big_note' => !empty($inquiryEntry['big_note']) ? $inquiryEntry['big_note'] : '',
                    'big_note_tag' => !empty($inquiryEntry['big_note_tag']) ? $inquiryEntry['big_note_tag'] : '',
                    'stock_code' => !empty($inquiryEntry['stock_code']) ? $inquiryEntry['stock_code'] : '',
                    'brand' => !empty($inquiryEntry['brand']) ? $inquiryEntry['brand'] : null,
                    'material_name_text' => !empty($inquiryEntry['material_name_text']) ? $inquiryEntry['material_name_text'] : null,
                    'line_type_id' => !empty($inquiryEntry['line_type_id']) ? $inquiryEntry['line_type_id'] : 0,
                    'amount_field' => !empty($inquiryEntry['amount_field']) ? $inquiryEntry['amount_field'] : null,
                    'budget_price' => !empty($inquiryEntry['budget_price']) ? $inquiryEntry['budget_price'] : 0,
                    'budget_amount' => !empty($inquiryEntry['budget_amount']) ? $inquiryEntry['budget_amount'] : 0,
                    'new_material_code' => !empty($inquiryEntry['new_material_code']) ? $inquiryEntry['new_material_code'] : null,
                    'boss_goods_id' => !empty($inquiryEntry['boss_goods_id']) ? $inquiryEntry['boss_goods_id'] : 0,
                    'quote_id' => $quoteId,
                    'asstpro_id' => !empty($inquiryEntry['asstpro_id']) ? $inquiryEntry['asstpro_id'] : 0,
                    'inquiry_qty' => !empty($inquiryEntry['qty']) ? $inquiryEntry['qty'] : 0,
                    'material_name' => !empty($entry['material_name']) ? $entry['material_name'] : '',
                    'qty' => !empty($entry['qty']) ? $entry['qty'] : 0,
                    'deli_address' => !empty($entry['deli_address']) ? $entry['deli_address'] : '',
                    'delive_method' => !empty($entry['delive_method']) ? $entry['delive_method'] : '',
                    'deli_at' => !empty($entry['deli_at']) ? $entry['deli_at'] : null,
                    'inquiry_entry_id' => !empty($entry['inquiry_entry_id']) ? $entry['inquiry_entry_id'] : 0,
                    'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0,
                    'price' => $price,
                    'tax_price' => $taxPrice,
                    'amount' => $amount,
                    'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null,
                    'quote_unit_id' => !empty($entry['quote_unit_id']) ? $entry['quote_unit_id'] : null,
                    'precision' => !empty($entry['precision']) ? $entry['precision'] : 0,
                    'pobill_no' => !empty($entry['pobill_no']) ? $entry['pobill_no'] : '',
                    'pcbill_no' => !empty($entry['pcbill_no']) ? $entry['pcbill_no'] : '',
                    'quote_curr' => !empty($entry['quote_curr']) ? $entry['quote_curr'] : '',
                    'exrate' => !empty($entry['exrate']) ? $entry['exrate'] : '0',
                    'warranty_period' => !empty($entry['warranty_period']) ? $entry['warranty_period'] : '',
                    'new_qty' => !empty($entry['new_qty']) ? $entry['new_qty'] : 0,
                    'price_field' => !empty($entry['price_field']) ? $entry['price_field'] : '',
                    'boss_goods' => !empty($entry['boss_goods']) ? $entry['boss_goods'] : 0,
                    'spec_model' => !empty($entry['spec_model']) ? $entry['spec_model'] : '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $admin->user_id,
                ];
                QuoteEntry::insertGetId($entryData);
//                $entry['entry_id'] = $entryId;
//                (new EntrySubRepo())->updateData($inquiryId, $entry);
            }
            return [$sumAmount, $sumTax, $sumTaxAmount, $sumQty];
        }
        return [$sumAmount, $sumTax, $sumTaxAmount, $sumQty];
    }

    /**
     * 导出
     * @param $inquiryId
     * @return array
     */
    public function export(int $inquiryId, Request $request) {
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $entryTable = $this->model->getTable();
        $ientryTable = (new InquiryEntry)->getTable();
        $quoteId = $request->quote_id;
        $qurey = InquiryEntry::
                selectRaw('ie.id AS inquiry_entry_id,'
                        . 'ie.material_name as ie_material_name,ie.specification_model AS ie_specification_model, '
                        . 'ie.material_desc,ie.inquiry_unit_id,'
                        . 'e.material_name,e.qty,e.specification_model,'
                        . 'e.tax_rate,e.price,e.tax_price,e.quote_unit_id,e.note,'
                        . 'e.pobill_no,e.pcbill_no,e.quote_curr,e.material_desc,'
                        . 'ie.material_desc AS ie_material_desc,e.specification_model,e.spec_model,'
                        . 'e.exrate,e.warranty_period,e.new_qty,ie.stock_code,'
                        . 'e.created_at,e.created_by,ie.inquire_qty as inquiry_qty')
                ->from($ientryTable . ' as ie')
                ->leftJoin($entryTable . ' as e', function ($join)use ($quoteId) {
            $join->on('ie.id', '=', 'e.inquiry_entry_id')
            ->where('e.deleted_flag', 'N')
            ->where('e.quote_id', $quoteId);
        });
        $qurey->where('ie.inquiry_id', $inquiryId);
        $qurey->where('ie.deleted_flag', 'N');
        $object = $qurey->orderBy('ie.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $inquiry = Inquiry::where('id', $inquiryId)
                ->select('bill_no')
                ->first();
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');
        (new UnitRepo)->setUnits($list, 'quote_unit_id', 'quote_unit_name');
        $quoteEntryArr = $this->setQuoteEntrys($request);
        foreach ($list as &$entry) {
            $entry['inquiry_qty'] = number_format($entry['inquiry_qty'], 4, '.', '');
            $entry['inquiry_no'] = $inquiry->bill_no;
            if (!empty($quoteEntryArr[$entry['inquiry_entry_id']])) {
                $quoteEntry = $quoteEntryArr[$entry['inquiry_entry_id']];
                $entry['qty'] = !empty($quoteEntry['qty']) ? number_format($quoteEntry['qty'], 4, '.', '') : $entry['inquiry_qty'];
                $entry['tax_price'] = !empty($quoteEntry['tax_price']) ? number_format($quoteEntry['tax_price'], 4, '.', '') : '';
                $entry['tax_rate'] = !empty($quoteEntry['tax_rate']) ? number_format($quoteEntry['tax_rate'], 4, '.', '') : '';
                $entry['tax_rate_id'] = !empty($quoteEntry['tax_rate_id']) ? $quoteEntry['tax_rate_id'] : null;
                $entry['price'] = !empty($quoteEntry['price']) ? number_format($quoteEntry['price'], 4, '.', '') : '0';
            } else {

                $entry['qty'] = !empty($entry['qty']) ? number_format($entry['qty'], 4, '.', '') : $entry['inquiry_qty'];
                $entry['tax_price'] = !empty($entry['tax_price']) ? number_format($entry['tax_price'], 4, '.', '') : '';
                $entry['tax_rate'] = !empty($entry['tax_rate']) ? number_format($entry['tax_rate'], 4, '.', '') : '';
                $entry['tax_rate_id'] = !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null;
                $entry['price'] = !empty($entry['price']) ? number_format($entry['price'], 4, '.', '') : '';
            }
        }
        $headName = $this->getHeadName();
        $xlsName = "物料报价信息_" . $inquiry->bill_no; //文件名称
        return $this->downloadExcel($xlsName, $list, $headName);
    }

    public function setQuoteEntrys($request) {
        $params = $request->all();
        if (empty($params['entrys'])) {
            return [];
        }
        $ret = [];
        foreach ($params['entrys'] AS $entry) {
            $ret[$entry['inquiry_entry_id']] = $entry;
        }
        return $ret;
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
        $fillstyle = $redStyleArray = $styleArray = $this->styleArray;
        $redStyleArray['font']['color']['rgb'] = 'FF0000';
        $fillstyle['fill'] = $redStyleArray['fill'] = [
            'fillType' => 'linear',
            'rotation' => 0.0,
            'startColor' => [
                'rgb' => 'EEEEEE'
            ],
            'endColor' => [
                'argb' => 'FFEEEEEE'
            ]
        ];
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '物料报价信息');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $clumnName = strtoupper(chr($i));
            $this->setExcelRow($sheet, $clumnName, 2, $head[$i - 65], 20);
            if (strpos($head[$i - 65], '*') !== false) {
                $sheet->getStyle($clumnName . 2)->applyFromArray($redStyleArray);
            } else {
                $sheet->getStyle($clumnName . 2)->applyFromArray($fillstyle);
            }
        }
        $sheet->getComment('K2')->getText()->createTextRun('税率取值为0,3,5,6,9,13');
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始：
            $this->setExcelRow($sheet, 'A', $row, ' ' . $item['inquiry_entry_id'], 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['inquiry_no'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, !empty($item['material_name']) ? $item['material_name'] : $item['ie_material_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, !empty($item['stock_code']) ? $item['stock_code'] : null, 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, !empty($item['specification_model']) ? $item['specification_model'] : $item['ie_specification_model'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, !empty($item['material_desc']) ? $item['material_desc'] : $item['ie_material_desc'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['inquiry_unit_id_name'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['inquiry_qty'], 24);
            $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'I', $row, !empty($item['quote_unit_id_name']) ? $item['quote_unit_id_name'] : $item['inquiry_unit_id_name'], 24);
            $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'J', $row, !empty($item['qty']) ? $item['qty'] : '', 24);
            $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'K', $row, !empty($item['tax_rate']) ? $item['tax_rate'] : '', 24);
            $sheet->getStyle('K' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'L', $row, !empty($item['tax_price']) ? $item['tax_price'] : '', 24);
            $sheet->getStyle('L' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'N', $row, !empty($item['warranty_period']) ? $item['warranty_period'] : '', 24);
            $sheet->getStyle('N' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'N', $row, !empty($item['node']) ? $item['node'] : '', 24);
            $sheet->getStyle('N' . $row)->applyFromArray($styleArray);

            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:N1')
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

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '关联ID',
            '询价单号',
            '存货编码',
            '物料名称',
            '规格型号',
            '物料描述',
            '询价单位',
            '询价数量',
            '报价单位',
            '报价数量',
            '*税率%',
            '*含税单价',
            '质保期',
            '备注',
        ];
    }

    /**
     * @desc 处理业务SKU参数
     *
     * @param array $importData 规格属性
     * @return bool
     * @author zhongyg
     * @time 2019-06-14
     */
    public function importItemHandler($importData, $quoteId = null) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $data = $this->dataTrim($importData);
        $list = [];
        $inquiryEntryIds = [];
        $taxRateArr = $this->getTaxRateArr();
        $unitArr = $this->getUnitNameArr();
        foreach ($data as $v) {
            if (empty($v[0]) || in_array(trim($v[0]), $inquiryEntryIds)) {
                continue;
            }
            $inquiryEntryIds[] = trim($v[0]);
            $item['quote_id'] = $quoteId;
            $item['inquiry_entry_id'] = !empty($v[0]) ? trim($v[0]) : '';
            $item['inquiry_no'] = !empty($v[1]) ? trim($v[1]) : '';
            $item['material_name'] = !empty($v[3]) ? trim($v[3]) : '';
            $item['quote_unit_id_name'] = !empty($v[8]) ? trim($v[8]) : '';
            if (!empty($item['quote_unit_id_name']) && !empty($unitArr[$item['quote_unit_id_name']])) {
                $item['quote_unit_id'] = $unitArr[$item['quote_unit_id_name']]['id']; //申报要素 
            } else {
                $item['quote_unit_id'] = 0;
            }
            $item['inquire_qty'] = !empty($v[9]) ? number_format(trim($v[9]), !empty($unitArr[$item['quote_unit_id_name']]) ? $unitArr[$item['quote_unit_id_name']]['precision'] : 2, '.', '') : 0;
            $item['qty'] = !empty($v[9]) ? number_format(trim($v[9]), !empty($unitArr[$item['quote_unit_id_name']]) ? $unitArr[$item['quote_unit_id_name']]['precision'] : 2, '.', '') : 0; //报价数量  
            $item['tax_rate'] = !empty($v[10]) ? trim($v[10]) : 0; //税率%

            if (!empty($item['tax_rate']) && !empty($taxRateArr[intval($item['tax_rate'])])) {
                $item['tax_rate_id'] = $taxRateArr[intval($item['tax_rate'])]; //申报要素 
            } else {
                $item['tax_rate_id'] = 0;
            }
            $item['tax_price'] = !empty($v[11]) ? number_format(trim($v[11]), 2, '.', '') : 0.00; //含税单价
            $item['warranty_period'] = !empty($v[12]) ? number_format(trim($v[12]), 2, '.', '') : 0.00; //含税单价
            $item['node'] = !empty($v[13]) ? number_format(trim($v[13]), 2, '.', '') : 0.00; //含税单价
            $item['spec_model'] = !empty($v[14]) ? number_format(trim($v[14]), 2, '.', '') : 0.00; //含税单价
            $item['specification_model'] = !empty($v[14]) ? number_format(trim($v[14]), 2, '.', '') : 0.00; //含税单价
            $list[] = $item;
        }
        (new InquiryEntryRepo)->setEntryInfos($list);

        (new UnitRepo)->setUnitIds($list, 'quote_unit_name', 'quote_unit_name');
        $dataList = $list;
        if ($quoteId) {
            $inquiryId = Quote::where('id', $quoteId)->value('inquiry_id');
            if (empty($inquiryId)) {
                return $list;
            }
            $taxCalType = Inquiry::where('id', $inquiryId)->value('tax_cal_type');
            if (empty($taxCalType)) {
                return $list;
            }
            foreach ($dataList as &$entry) {
                unset($entry['quote_unit_id_name']);
                unset($entry['quote_unit_name']);
                unset($entry['inquiry_unit_id_name']);
                unset($entry['inquiry_no']);
                $qty = !empty($entry['qty']) ? $entry['qty'] : 0;
                $price = !empty($entry['price']) ? $entry['price'] : 0;
                $taxPrice = !empty($entry['tax_price']) ? $entry['tax_price'] : 0;
                $taxRate = !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0;
                if ($price !== '' && $taxPrice !== '') {
                    $this->getPrice($price, $taxPrice, $taxRate, $taxCalType);
                    $sumQty += $qty;
                    $amount = round($qty * $price, 8);
                    $sumAmount += $amount;
                    $taxAmount = round($qty * $taxPrice, 8);
                    $sumTaxAmount += $taxAmount;
                    $tax = round($qty * ($taxPrice - $price), 8);
                    $sumTax += $tax;
                } else {
                    $price = null;
                    $taxPrice = null;
                    $amount = null;
                    $taxAmount = null;
                    $tax = null;
                }
                $entry['inquiry_id'] = $inquiryId;
                $entry['price'] = $price;
                $entry['tax_price'] = $taxPrice;
                $entry['amount'] = $amount;
                $entry['tax'] = $tax;
                $entry['tax_amount'] = $taxAmount;
            }
            QuoteEntry::upsert($dataList, ['quote_id', 'inquiry_entry_id'], [
                'material_name',
                'quote_unit_id',
                'inquiry_id',
                'qty',
                'price',
                'amount',
                'tax',
                'tax_amount',
                'tax_rate',
                'tax_rate_id',
                'tax_price',
                'node',
                'warranty_period',
                'spec_model',
                'specification_model',
                    ]
            );
            Quote::where('id', $quoteId)->update(['sum_amount' => $sumAmount,
                'sum_tax' => $sumTax,
                'sum_tax_amount' => $sumTaxAmount,
                'sum_qty' => $sumQty,
            ]);
        }
        return $list;
    }

    public function getTaxRateArr() {
        $taxrateList = TaxRate::get()->toArray();
        $taxrateArr = [];
        foreach ($taxrateList as $taxRate) {
            $taxrateArr[intval($taxRate['tax_rate'])] = $taxRate['id'];
        }
        return $taxrateArr;
    }

    public function getUnitNameArr() {
        $unitList = Unit::get()->toArray();
        $ret = [];
        foreach ($unitList as $unit) {
            $ret[$unit['name']] = $unit;
        }
        return $ret;
    }

    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-02-02
     */
    function dataTrim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_string($data)) {
            return trim($data);
        }
        return $data;
    }

    private function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    public function download2local($tmpSavePath, $remoteFile, $attach_name) {
        //设置本地临时保存目录
        $localFullFileName = $tmpSavePath . mb_convert_encoding(urldecode(basename($attach_name)), 'GB2312', 'UTF-8');
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $file = fopen($remoteFile, 'rb', null, $context);
        if ($file) {
            $newf = fopen($localFullFileName, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $localFullFileName;
    }

    public function import(Request $request) {
        $remoteFile = $request->file_url;
        $attachName = $request->attach_name;
        $quoteId = $request->quote_id;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData, $quoteId);
    }

    public function ready2import($localFile, $pIndex = 0) {
        //获取文件类型
        $fileType = IOFactory::identify($localFile);
        //创建PHPExcel读取对象
        $objReader = IOFactory::createReader($fileType);
        //加载文件并读取
        $officeSheet = $objReader->load($localFile);
        $data = $officeSheet->getSheet($pIndex)->toArray();
        return $data;
    }

}
