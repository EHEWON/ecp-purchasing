<?php

namespace App\Modules\Admin\Repository\Compare;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Compare\Entry,
    Unit
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    SupplierBaseRepo,
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

class EntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Entry();
        parent::__construct($this->model);
    }

    public function getList(int $compareId) {
        if (empty($compareId)) {
            return [];
        }
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->selectRaw('id,compare_id,supplier_id,seq,material_id,material_name_text,material_desc,unit_id,inquire_qty,qty,price,tax_price,tax_rate,tax,amount');
        $qurey->where('compare_id', $compareId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new CurrencyRepo)->setCurrencys($list, 'quote_curr', ['quote_curr_name' => 'name',
            'quote_curr_sign' => 'sign',
            'quote_curr_number' => 'number',
        ]);
        (new UnitRepo)->setUnits($list, 'unit_id', 'unit_name');
        return $list;
    }

    public function details(int $compareId, $data = []) {
        $entryTable = (new Entry)->getTable();
        $qurey = $this->model
                        ->selectRaw('a.*,en.`boss_goods_id`,en.stock_code,en.specification_model')
                        ->from($entryTable . ' as a')
                        ->join('inquiry_entry as en', 'a.material_id', '=', 'en.id')
                        ->where('a.compare_id', $compareId)->where('a.deleted_flag', 'N');
        if (!empty($data)) {
            $qurey->where($data);
        }
        $object = $qurey->orderBy('material_id', 'ASC')
                ->orderBy('supplier_id', 'ASC')
                ->get();
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'unit_id', 'unit_name');
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        $ret = [];
        foreach ($list as $item) {
            $item['inquire_qty'] = number_format($item['inquire_qty'], 2, '.', '');
            $item['qty'] = number_format($item['qty'], 2, '.', '');
            if (empty($ret[$item['material_id']])) {
                $ret[$item['material_id']] = [
                    'material_name_text' => $item['material_name_text'],
                    'inquire_qty' => $item['inquire_qty'],
                    'unit_name' => $item['unit_name'],
                    'material_desc' => $item['material_desc'],
                    'stock_code' => $item['stock_code'],
                    'specification_model' => $item['specification_model'],
                    'boss_goods_id' => $item['boss_goods_id'],
                ];
            }
            $item['qty'] = !empty($item['qty']) ? $item['qty'] : $item['qty'];
            $item['price'] = number_format($item['price'], 2, '.', '');
            $item['tax_price'] = number_format($item['tax_price'], 2, '.', '');
            $item['amount'] = number_format($item['amount'], 2, '.', '');
            $item['tax_rate'] = number_format($item['tax_rate'], 2, '.', '');
            $item['tax'] = number_format($item['tax'], 2, '.', '');
            $item['tax_amount'] = number_format($item['tax_amount'], 2, '.', '');
            if ($item['adopt_flag'] == 'true') {
                $item['adopt_flag'] = true;
            } else {
                $item['adopt_flag'] = false;
            }
            $item['quote_curr'] = $item['quote_curr'];
            $item['cfm_tax'] = $item['tax'];
            $item['cfm_tax_amount'] = $item['tax_amount'];
            (new CurrencyRepo)->setCurrency($item, 'quote_curr', 'quote_curr_name');
            $ret[$item['material_id']]['quotes'][] = $item;
        }
        $result = [];
        foreach ($ret as $item) {
            $result[] = $item;
        }
        return $result;
    }

    public function updateData(int $compareId, Request $request) {
        $admin = Auth::guard('admin')->user();
        Entry::where('compare_id', $compareId)->delete();
        if (!empty($request->entrys)) {
            foreach ($request->entrys as $key => $entry) {
                $entryData = [
                    'compare_id' => $compareId,
                    'seq' => $key + 1,
                    'quote_id' => !empty($entry['quote_id']) ? $entry['quote_id'] : null,
                    'supplier_id' => !empty($entry['supplier_id']) ? $entry['supplier_id'] : null,
                    'material_id' => !empty($entry['material_id']) ? $entry['material_id'] : null,
                    'inquiry_entry_id' => !empty($entry['material_id']) ? $entry['material_id'] : null,
                    'material_name_text' => !empty($entry['material_name_text']) ? $entry['material_name_text'] : null,
                    'material_desc' => !empty($entry['material_desc']) ? $entry['material_desc'] : null,
                    'unit_id' => !empty($entry['unit_id']) ? $entry['unit_id'] : null,
                    'unit_name' => !empty($entry['unit_name']) ? $entry['unit_name'] : null,
                    'qty' => !empty($entry['qty']) ? $entry['qty'] : null,
                    //'inquiry_qty' => !empty($entry['inquiry_qty']) ? $entry['inquiry_qty'] : null,
                    'inquire_qty' => !empty($entry['inquire_qty']) ? $entry['inquire_qty'] : null,
                    'price' => !empty($entry['price']) ? $entry['price'] : 0.000000,
                    'tax_price' => !empty($entry['tax_price']) ? $entry['tax_price'] : 0.000000,
                    'amount' => !empty($entry['amount']) ? $entry['amount'] : 0.000000,
                    'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : 0.000000,
                    'tax' => !empty($entry['tax']) ? $entry['tax'] : 0.000000,
                    'tax_amount' => !empty($entry['tax_amount']) ? $entry['tax_amount'] : 0.000000,
                    'adopt_flag' => !empty($entry['adopt_flag']) ? $entry['adopt_flag'] : null,
                    'quote_curr' => !empty($entry['quote_curr']) ? $entry['quote_curr'] : null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $admin->user_id,
                    'warranty_period' => !empty($entry['warranty_period']) ? $entry['warranty_period'] : null,
                ];
                $entryId = Entry::insertGetId($entryData);
            }
        }
    }

    /**
     * 导出
     * @param $inquiryId
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
    public function importItemHandler($importData, $inquiryId) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $data = $this->dataTrim($importData);
        $list = [];
        $unitRepo = new UnitRepo();
        foreach ($data as $v) {
            $item['inquiry_id'] = $inquiryId;
            $item['material_name'] = trim($v[0]); //执行标准
            $item['material_desc'] = trim($v[1]); //申报要素          
            $item['inquire_qty'] = intval($v[2]); //申报要素     
            $item['inquiry_unit_id'] = Unit::where('name', $v['3'])->value('id'); //申报要素
            $list[] = $item;
        }
        Entry::where('inquiry_id', $inquiryId)->delete();
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
        $compareId = $request->compare_id;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData, $compareId);
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
