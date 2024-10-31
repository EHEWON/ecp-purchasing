<?php

namespace App\Modules\Admin\Repository\Compare;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Compare\CompareQuote,
    Quote\Quote,
    Unit
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    PaycondRepo,
    Quote\SubRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class CompareQuoteRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new CompareQuote();
        parent::__construct($this->model);
    }

    public function getList(int $compareId) {
        if (empty($compareId)) {
            return [];
        }
        $entryTable = $this->model->getTable();
        $quoteTable = (new Quote)->getTable();
        $qurey = $this->model
                ->selectRaw('e.*,q.settle_type_id')
                ->from($entryTable . ' as e')
                ->join($quoteTable . ' AS q', function($join) {
            $join->on('q.id', '=', 'e.quote_id')
            ->where('q.bill_status', 'C');
        });
        $qurey->where('e.compare_id', $compareId);
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            if ($item['adopt_flag'] == 'true') {
                $item['adopt_flag'] = true;
            } else {
                $item['adopt_flag'] = false;
            }
            $item['tax_cal_type_name'] = getTaxCalTypeText($item['tax_cal_type']);
            $item['sum_tax_amount'] = $item['total_amount'];
        }
        (new CurrencyRepo)->setCurrencys($list, 'quote_curr', ['quote_curr_name' => 'name',
            'quote_curr_sign' => 'sign',
            'quote_curr_number' => 'number',
        ]);
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        (new PaycondRepo)->setPayconds($list, 'payment_terms', ['payment_terms_name' => 'name', 'payment_terms_number' => 'number']);
        (new SettleMentTypeRepo)->setSettleMentTypes($list, 'settle_type_id', 'settle_type_name');
        return $list;
    }

    public function updateData(int $compareId, Request $request) {
        $admin = Auth::guard('admin')->user();
        CompareQuote::where('compare_id', $compareId)->delete();
        if (!empty($request->quotes)) {
            foreach ($request->quotes as $key => $quotes) {
                $quotesData = [
                    'compare_id' => $compareId,
                    'supplier_id' => !empty($quotes['supplier_id']) ? $quotes['supplier_id'] : null,
                    'quote_no' => !empty($quotes['quote_no']) ? $quotes['quote_no'] : null,
                    'quote_id' => !empty($quotes['quote_id']) ? $quotes['quote_id'] : null,
                    'total_amount' => !empty($quotes['total_amount']) ? $quotes['total_amount'] : 0.000000,
                    'adopt_total_amount' => !empty($quotes['adopt_total_amount']) ? $quotes['adopt_total_amount'] : 0.000000,
                    'tax_cal_type' => !empty($quotes['tax_cal_type']) ? $quotes['tax_cal_type'] : null,
                    'delivery_date' => !empty($quotes['delivery_date']) ? $quotes['delivery_date'] : null,
                    'warranty_period' => !empty($quotes['warranty_period']) ? $quotes['warranty_period'] : null,
                    'payment_terms' => !empty($quotes['payment_terms']) ? $quotes['payment_terms'] : null,
                    'other_pay_terms_info' => !empty($quotes['other_pay_terms_info']) ? $quotes['other_pay_terms_info'] : null,
                    'adopt_flag' => !empty($quotes['adopt_flag']) ? $quotes['adopt_flag'] : null,
                    'quote_curr' => !empty($quotes['quote_curr']) ? $quotes['quote_curr'] : null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $admin->user_id,
                ];
                $quoteId = CompareQuote::insertGetId($quotesData);
            }
        }
        (new SubRepo)->decision($compareId, $request);
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc 获取行业
     */
    public function setStrSuppliersByCompareId(array &$arr, string $field = 'compare_id', $fieldKey = 'supplier_names') {
        if (empty($arr)) {
            return;
        }
        $userId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $userId = $arr[$field];
        }
        $compare_id = $arr[$field];
        $arr[$fieldKey] = '';
        if (empty($compare_id)) {
            return $arr;
        }
        //$users = $this->getList($compare_id);
        $entryTable = $this->model->getTable();
        $qurey = $this->model->from($entryTable . ' as e');
        $qurey->where('e.compare_id', $compare_id);
        $qurey->where('e.deleted_flag', 'N');
        $qurey->where('e.adopt_flag', 'true');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        $user_realnames = array_column($list, 'supplier_name');
        $arr[$fieldKey] = implode(',', $user_realnames);
    }

    /**
     * 导出
     * @param $compareId
     * @return array
     */
    public function export(int $compareId) {
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.id,e.material_id,e.material_desc,e.inquiry_unit_id,'
                . 'e.material_name,e.inquire_qty,e.material_code,stock_code,brand,'
                . 'e.specification_model,e.deli_type_id,e.deli_date,e.deli_addr');
        $qurey->where('e.compare_id', $compareId);
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $billNo = Inquiry::where('id', $compareId)->value('bill_no');
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');
        foreach ($list as &$item) {
            $item['inquire_qty'] = number_format($item['inquire_qty'], 4, '.', '');
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

            if ($clumnName === 'B') {
                $sheet->getStyle($clumnName . 2)->applyFromArray($fillstyle);
            } else {
                $sheet->getStyle($clumnName . 2)->applyFromArray($redStyleArray);
            }
        }
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始：

            $this->setExcelRow($sheet, 'A', $row, $item['material_name'], 24);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['material_desc'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['inquire_qty'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['inquiry_unit_id_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
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
            '*询价数量',
            '*询价单位',
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
    public function importItemHandler($importData, $compareId) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $data = $this->dataTrim($importData);
        $list = [];
        $unitRepo = new UnitRepo();
        foreach ($data as $v) {
            $item['compare_id'] = $compareId;
            $item['material_name'] = trim($v[0]); //执行标准
            $item['material_desc'] = trim($v[1]); //申报要素          
            $item['inquire_qty'] = intval($v[2]); //申报要素     
            $item['inquiry_unit_id'] = Unit::where('name', $v['3'])->value('id'); //申报要素
            $list[] = $item;
        }
        Entry::where('compare_id', $compareId)->delete();
        return Entry::insert($list);
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
        $inquiryId = $request->inquiry_id;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData, $inquiryId);
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
