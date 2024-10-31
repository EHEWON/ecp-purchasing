<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Inquiry\Entry,
    Inquiry\Inquiry,
    Unit
};
use App\Modules\Admin\Repository\UnitRepo;
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

    public function getList(int $inquiryId, $turns = null) {
        if (empty($inquiryId)) {
            return [];
        }
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.id,e.material_id,e.material_desc,e.inquiry_unit_id,'
                . 'e.material_name,e.inquire_qty,e.material_code,stock_code,brand,'
                . 'e.specification_model,e.deli_type_id,e.deli_date,e.deli_addr,warranty_period,e.precision,e.boss_goods_id');
        $qurey->where('e.inquiry_id', $inquiryId);
        $qurey->where('e.deleted_flag', 'N');
        if (!empty($turns)) {
            $qurey->whereRaw('FIND_IN_SET(' . $turns . ',e.turns)');
        }
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'inquiry_unit_id', 'inquiry_unit_name');

        foreach ($list as &$item) {
            $item['inquire_qty'] = number_format($item['inquire_qty'], 4, '.', '');
            $item['deli_type'] = $item['deli_type_id'] == '0' ? '发货' : '自提';
        }
        return $list;
    }

    public function updateData(int $inquiryId, Request $request, $turns = 1) {
        $admin = Auth::guard('admin')->user();
        if ($turns == 1) {
            Entry::where('inquiry_id', $inquiryId)->delete();
        }
        if (empty($request->entrys)) {
            return;
        }
        $entrys = $request->entrys;
        (new UnitRepo)->setUnitPrecisions($entrys, 'inquiry_unit_id');
        foreach ($entrys as $key => $entry) {
            if (empty(trim($entry['material_name'])) && empty(trim($entry['inquire_qty'])) && empty(trim($entry['inquiry_unit_id']))) {
                continue;
            }
            $entryObj = $turns > 1 && !empty($entry['id']) ? Entry::where('inquiry_id', $inquiryId)
                            ->where('id', trim($entry['id']))
                            ->where('material_name', trim($entry['material_name']))
                            ->where('inquire_qty', trim($entry['inquire_qty']))
                            ->where('inquiry_unit_id', trim($entry['inquiry_unit_id']))
                            ->where('material_desc', trim($entry['material_desc']))
                            ->first() : null;
            $entryData = [
                'inquiry_id' => $inquiryId,
                'seq' => $key + 1,
                'turns' => $turns > 1 && !empty($entryObj) ? $entryObj->turns . ',' . $turns : $turns,
                'material_id' => !empty($entry['material_id']) ? $entry['material_id'] : null,
                'material_desc' => !empty($entry['material_desc']) ? $entry['material_desc'] : null,
                'asstpro_id' => !empty($entry['asstpro_id']) ? $entry['asstpro_id'] : null,
                'unit_id' => !empty($entry['unit_id']) ? $entry['unit_id'] : null,
                'qty' => !empty($entry['qty']) ? $entry['qty'] : null,
                'warranty_period' => !empty($entry['warranty_period']) ? $entry['warranty_period'] : null,
                'deli_date' => !empty($entry['deli_date']) ? $entry['deli_date'] : null,
                'deli_addr' => !empty($entry['deli_addr']) ? $entry['deli_addr'] : null,
                'deli_type_id' => !empty($entry['deli_type_id']) ? $entry['deli_type_id'] : 0,
                'price' => !empty($entry['price']) ? $entry['price'] : 0.000000,
                'tax_price' => !empty($entry['tax_price']) ? $entry['tax_price'] : 0.000000,
                'dct_rate' => !empty($entry['dct_rate']) ? $entry['dct_rate'] : 0.000000,
                'dct_amount' => !empty($entry['dct_amount']) ? $entry['dct_amount'] : 0.000000,
                'precision' => !empty($entry['precision']) ? $entry['precision'] : 0,
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
                'warranty_period' => !empty($entry['warranty_period']) ? $entry['warranty_period'] : '',
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
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $admin->user_id,
            ];
            if (empty($entryObj)) {
                $entryId = Entry::insertGetId($entryData);
                $entry['entry_id'] = $entryId;
            } else {
                Entry::where('id', $entryObj->id)->update($entryData);
                $entry['entry_id'] = $entryObj->id;
            }

            (new EntrySubRepo())->updateData($inquiryId, $entry);
        }
    }

    /**
     * 导出
     * @param $inquiryId
     * @return array
     */
    public function export(int $inquiryId) {
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $entryTable = $this->model->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->selectRaw('e.id,e.material_id,e.material_desc,e.inquiry_unit_id,'
                . 'e.material_name,e.inquire_qty,e.material_code,stock_code,brand,'
                . 'e.specification_model,e.deli_type_id,e.deli_date,e.deli_addr');
        $qurey->where('e.inquiry_id', $inquiryId);
        $qurey->where('e.deleted_flag', 'N');
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $billNo = Inquiry::where('id', $inquiryId)->value('bill_no');
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
            $this->setExcelRow($sheet, 'A', $row, $item['stock_code'], 24);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['material_name'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['specification_model'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['material_desc'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['inquire_qty'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['inquiry_unit_id_name'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
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
            '存货编码',
            '*物料名称',
            '规格型号',
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
    public function importItemHandler($importData) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
//        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $data = $this->dataTrim($importData);
        $list = [];
//        $unitRepo = new UnitRepo();
        $unitArr = $this->getUnitNameArr();
        foreach ($data as $key => $v) {
            $item['inquiry_ind'] = ($key + 1);
            $item['stock_code'] = !empty($v[0]) ? trim($v[0]) : ''; //执行标准
            $item['material_name'] = !empty($v[1]) ? trim($v[1]) : ''; //执行标准
            $item['specification_model'] = !empty($v[2]) ? trim($v[2]) : ''; //申报要素   
            $item['material_desc'] = !empty($v[3]) ? trim($v[3]) : ''; //申报要素     
            $item['inquire_qty'] = !empty($v[4]) ? trim($v[4]) : 0; //申报要素    
            $item['inquiry_unit_id_name'] = !empty($v[5]) ? trim($v[5]) : 0;
            if (!empty($item['inquiry_unit_id_name']) && !empty($unitArr[$item['inquiry_unit_id_name']])) {
//                $unit = Unit::where('name', $item['quote_unit_id_name'])->first();
                $item['inquiry_unit_id'] = $unitArr[$item['inquiry_unit_id_name']]; //申报要素
            } else {
                $item['inquiry_unit_id'] = 0;
            }
            $list[] = $item;
        }
        return $list;
//        Entry::where('inquiry_id', $inquiryId)->delete();
//        return Entry::insert($list);
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

    public function setEntrys(&$list) {
        if (empty($list)) {
            return;
        }
        $inquiryIds = [];
        foreach ($list as &$inquiry) {
            $inquiry['entrys'] = [];
            $inquiryIds[] = $inquiry['id'];
        }
        if (empty($inquiryIds)) {
            return;
        }
        $entryObj = $this->model
                ->selectRaw('inquiry_id,material_name_text,material_name,material_desc,inquire_qty,'
                        . 'inquiry_unit_id,warranty_period,specification_model,stock_code')
                ->whereIn('inquiry_id', $inquiryIds)
                ->where('deleted_flag', 'N')
                ->get();
        if (empty($entryObj)) {
            return;
        }
        $entryList = $entryObj->toArray();
        (new UnitRepo)->setUnits($entryList, 'inquiry_unit_id', 'inquiry_unit_name');
        $entryArr = [];
        foreach ($entryList as $entry) {
            $entryArr[$entry['inquiry_id']][] = $entry;
        }
        foreach ($list as &$inquiry) {
            if (empty($entryArr[$inquiry['id']])) {
                continue;
            }
            $inquiry['entrys'] = $entryArr[$inquiry['id']];
        }
    }

    public function setEntryInfos(&$list) {
        if (empty($list)) {
            return;
        }
        $inquiryEntryIds = array_column($list, 'inquiry_entry_id');
        if (empty($inquiryEntryIds)) {
            return;
        }
        $entryObj = $this->model
                ->selectRaw('id,material_name_text,material_name,material_desc,inquire_qty,'
                        . 'inquiry_unit_id,warranty_period,specification_model,stock_code,`precision`')
                ->whereIn('id', $inquiryEntryIds)
                ->where('deleted_flag', 'N')
                ->get();
        if (empty($entryObj)) {
            return;
        }
        $entryList = $entryObj->toArray();
        (new UnitRepo)->setUnits($entryList, 'inquiry_unit_id', 'inquiry_unit_name');
        $entryArr = [];
        foreach ($entryList as $entry) {
            $entryArr[$entry['id']] = $entry;
        }
        $ret = [];
        foreach ($list as &$qentry) {
            if (empty($entryArr[$qentry['inquiry_entry_id']])) {
                continue;
            }
            $inquiryEntry = $entryArr[$qentry['inquiry_entry_id']];
            $qentry['stock_code'] = $inquiryEntry['stock_code'];
            $qentry['material_desc'] = $inquiryEntry['material_desc'];
            $qentry['specification_model'] = $inquiryEntry['specification_model'];
            $qentry['inquire_qty'] = number_format($inquiryEntry['inquire_qty'], intval($inquiryEntry['precision']), '.', '');
            $qentry['inquiry_unit_id_name'] = $inquiryEntry['inquiry_unit_id_name'];
            $qentry['inquiry_unit_id'] = $inquiryEntry['inquiry_unit_id'];
            $ret[] = $qentry;
        }
        $list = $ret;
    }

}
