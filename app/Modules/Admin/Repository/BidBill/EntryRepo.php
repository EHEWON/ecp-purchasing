<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    BidBill\BidBillEntry,
    TaxRate,
    Unit
};
use App\Modules\Admin\Repository\{
    SupplierBaseRepo,
    UnitRepo
};
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class EntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new BidBillEntry();
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
        $list = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($list, 'win_supplier_id', 'win_supplier_name');
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

    public function getPrice(&$price, &$taxPrice, $taxRate, $taxCalType) {

        switch ($taxCalType) {
            case '1'://价外税(含税)
                $price = round($taxPrice / (1 + $taxRate / 100), 8);
                return;
            case '3'://价内税(含税)
                $price = round($taxPrice * (1 - $taxRate / 100), 8);
                return;
            case '2'://价外税(不含税)
                $price = round($taxPrice / (1 + $taxRate / 100), 8);
                return;
        }
    }

    public function updateData(int $bidBillId, Request $request, $taxCalType = null) {
        BidBillEntry::where('bid_bill_id', $bidBillId)->delete();
        $sumAmount = $sumTax = $sumTaxAmount = $sumQty = 0;
        if (!empty($request->entrys)) {
            $entrys = $request->entrys;
            (new UnitRepo)->setUnitPrecisions($entrys, 'unit_id');
            foreach ($request->entrys as $key => $entry) {
                $qty = !empty($entry['qty']) ? $entry['qty'] : 0;
                $price = !empty($entry['price']) ? $entry['price'] : 0;
                $taxPrice = !empty($entry['tax_price']) ? $entry['tax_price'] : 0;
                $taxRate = !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0;
//                $sumQty += $qty;
//                $sumAmount += $amount;
//                $sumTaxAmount += $taxAmount;
//                $sumTax += $tax;
                if ($taxPrice !== '') {
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
                $entryData = [
                    'bid_bill_id' => $bidBillId,
                    'seq' => $key + 1,
                    'id' => !empty($entry['id']) ? $entry['id'] : null,
                    'material_id' => !empty($entry['material_id']) ? $entry['material_id'] : null,
                    'material_desc' => !empty($entry['material_desc']) ? $entry['material_desc'] : null,
                    'asstpro_id' => !empty($entry['asstpro_id']) ? $entry['asstpro_id'] : null,
                    'unit_id' => !empty($entry['unit_id']) ? $entry['unit_id'] : null,
                    'precision' => !empty($entry['precision']) ? $entry['precision'] : 0,
                    'qty' => $qty,
                    'price' => $price,
                    'tax_price' => $taxPrice,
                    'amount' => $amount,
                    'tax_rate' => $taxRate,
                    'tax' => $tax,
                    'tax_amount' => $taxAmount,
                    'deli_date' => !empty($entry['deli_date']) ? $entry['deli_date'] : null,
                    'deli_addr' => !empty($entry['deli_addr']) ? $entry['deli_addr'] : null,
                    'note' => !empty($entry['note']) ? $entry['note'] : null,
                    'entry_status' => !empty($entry['entry_status']) ? $entry['entry_status'] : null,
                    'win_price' => !empty($entry['win_price']) ? $entry['win_price'] : null,
                    'win_tax_price' => !empty($entry['win_tax_price']) ? $entry['win_tax_price'] : null,
                    'po_bill_no' => !empty($entry['po_bill_no']) ? $entry['po_bill_no'] : null,
                    'pc_bill_no' => !empty($entry['pc_bill_no']) ? $entry['pc_bill_no'] : null,
                    'project_id' => !empty($entry['project_id']) ? $entry['project_id'] : null,
                    'trace_id' => !empty($entry['trace_id']) ? $entry['trace_id'] : null,
                    'dct_rate' => !empty($entry['dct_rate']) ? $entry['dct_rate'] : null,
                    'dct_amount' => !empty($entry['dct_amount']) ? $entry['dct_amount'] : null,
                    'is_update_asinfo' => !empty($entry['is_update_asinfo']) ? $entry['is_update_asinfo'] : null,
                    'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null,
                    'win_supplier_id' => !empty($entry['win_supplier_id']) ? $entry['win_supplier_id'] : null,
                    'win_option' => !empty($entry['win_option']) ? $entry['win_option'] : null,
                    'win_amount' => !empty($entry['win_amount']) ? $entry['win_amount'] : null,
                    'material_name' => !empty($entry['material_name']) ? $entry['material_name'] : null,
                    'specification_model' => !empty($entry['specification_model']) ? $entry['specification_model'] : null,
//                    'material_unit_id' => !empty($entry['material_unit_id']) ? $entry['material_unit_id'] : null,
                    'material_name_text' => !empty($entry['material_name_text']) ? $entry['material_name_text'] : null,
                    'line_type_id' => !empty($entry['line_type_id']) ? $entry['line_type_id'] : null,
                ];
                BidBillEntry::insertGetId($entryData);
            }
            return [$sumAmount, $sumTax, $sumTaxAmount, $sumQty];
        }
        return [$sumAmount, $sumTax, $sumTaxAmount, $sumQty];
    }

    public function setEntrys(&$list) {
        if (empty($list)) {
            return;
        }
        $bidBillIds = [];
        foreach ($list as &$item) {
            $bidBillIds[] = $item['id'];
            $item['entrys'] = [];
        }
        $entryObj = $this->model
                ->selectRaw('id,bid_bill_id,material_name,unit_id,specification_model,qty,'
                        . 'win_supplier_id,win_amount,win_price,win_tax_price')
                ->whereIn('bid_bill_id', $bidBillIds)
                ->get();
        if (empty($entryObj)) {
            return;
        }
        $entryList = $entryObj->toArray();
        (new SupplierBaseRepo)->setSuppliers($entryList, 'win_supplier_id', 'win_supplier_name');
        (new UnitRepo)->setUnits($entryList, 'unit_id', 'unit_name');
        $entryArr = [];
        foreach ($entryList as $val) {
            $entryArr[$val['bid_bill_id']][] = $val;
        }

        foreach ($list as &$item) {
            if (!empty($entryArr[$item['id']])) {
                $item['entrys'] = $entryArr[$item['id']];
            }
        }
    }

    /**
     * 导出
     * @param $bidBillId
     * @return array
     */
    public function export(int $bidBillId) {
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.id,e.material_name,e.material_desc,e.unit_id,'
                . 'e.qty,e.price,e.tax_price,'
                . 'e.amount,e.tax_rate,e.tax,e.tax_amount,e.tax_rate_id');
        $qurey->where('e.bid_bill_id', $bidBillId);
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $billNo = \App\Common\Models\BidBill\BidBill::where('id', $bidBillId)->value('bill_no');
        $list = $object->toArray();
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
        $headName = $this->getHeadName();
        $xlsName = "物料信息_" . $billNo . date("YmdHis", time()) . uniqid(); //文件名称
        return $this->downloadExcel($xlsName, $list, $headName);
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
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '物料信息');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $clumnName = strtoupper(chr($i));
            $this->setExcelRow($sheet, $clumnName, 2, $head[$i - 65], 20);

            if (strpos('*', $clumnName)) {
                $sheet->getStyle($clumnName . 2)->applyFromArray($redStyleArray);
            } else {
                $sheet->getStyle($clumnName . 2)->applyFromArray($fillstyle);
            }
        }
        $sheet->getComment('F2')->getText()->createTextRun('税率取值为0,3,5,6,9,13');
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始:
            $this->setExcelRow($sheet, 'A', $row, $item['material_name'], 24);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['material_desc'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['qty'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['unit_id_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['price'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['tax_price'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['amount'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['tax_rate'], 24);
            $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'I', $row, $item['tax'], 24);
            $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'J', $row, $item['tax_amount'], 24);
            $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:D1')
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
            '*物料名称',
            '物料描述',
            '*数量',
            '*单位',
            '基准单价(￥)',
            '*基准含税单价(￥)',
            '金额(￥)',
            '*税率(%)',
            '税额(￥)',
            '价税合计(￥)'
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
    public function importItemHandler($importData) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $data = $this->dataTrim($importData);
        $list = [];
        $taxRateArr = $this->getTaxRateArr();
        $unitArr = $this->getUnitNameArr();
        foreach ($data as $v) {
            $item['material_name'] = !empty($v[0]) ? trim($v[0]) : ''; //执行标准
            $item['material_desc'] = !empty($v[1]) ? trim($v[1]) : ''; //申报要素     
            $item['qty'] = !empty($v[2]) ? trim($v[2]) : 0; //申报要素    
            $item['unit_id_name'] = !empty($v[3]) ? trim($v[3]) : 0;
            if (!empty($item['unit_id_name']) && !empty($unitArr[$item['unit_id_name']])) {
                $item['unit_id'] = $unitArr[$item['unit_id_name']]; //申报要素
            } else {
                $item['unit_id'] = 0;
            }

            $item['tax_price'] = !empty($v[4]) ? floatval($v[4]) : 0;
            $item['tax_rate'] = !empty($v[5]) ? floatval($v[5]) : 0;
            if (!empty($item['tax_rate']) && !empty($taxRateArr[intval($item['tax_rate'])])) {
                $item['tax_rate_id'] = $taxRateArr[intval($item['tax_rate'])]; //申报要素 
            } else {
                $item['tax_rate_id'] = 0;
            }

            $item['qty'] = number_format($item['qty'], 2, '.', '');
            $item['tax_price'] = number_format($item['tax_price'], 4, '.', '');
            $item['tax_rate'] = intval($item['tax_rate']);

            $list[] = $item;
        }
        return $list;
    }

    public function getUnitNameArr() {
        $unitList = Unit::get()->toArray();
        return array_column($unitList, 'id', 'name');
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
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $remoteFile = $request->file_url;
        $attachName = $request->attach_name;
//        $inquiryId = $request->inquiry_id;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData);
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

    public function getTaxRateArr() {
        $taxrateList = TaxRate::get()->toArray();
        $taxrateArr = [];
        foreach ($taxrateList as $taxRate) {
            $taxrateArr[intval($taxRate['tax_rate'])] = $taxRate['id'];
        }
        return $taxrateArr;
    }

}
