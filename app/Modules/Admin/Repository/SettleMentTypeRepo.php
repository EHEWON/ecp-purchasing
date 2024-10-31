<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\SettleMentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class SettleMentTypeRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SettleMentType();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'id,number,is_default,name,settle_ment_type,enable,description,modifier_id,modify_time') {
        $query = $this->model
          ->selectRaw($filed);
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $object = $query->orderBy('create_time', 'DESC')->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'modifier_id', 'modifier');
        foreach ($data as &$item) {
            $item['settle_ment_type_name'] = $this->handelSettleMentType($item['settle_ment_type']);
        }
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

        $query = $this->model->selectRaw('id,number,is_default,name,settle_ment_type,enable,description,modifier_id,modify_time');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUser($data, 'modifier_id', 'modifier');
        $data['settle_ment_type_name'] = $this->handelSettleMentType($data['settle_ment_type']);
        return $data;
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function edited($id, Request $request) {
        $admin = Auth::guard('admin')->user();
        return SettleMentType::where('id', $id)->update([
              'number' => trim($request->number),
              'status' => !empty($request->status) ? trim($request->status) : 'C',
              'modifier_id' => $admin->user_id,
              'modify_time' => date('Y-m-d H:i:s'),
              'number' => trim($request->number),
              'is_default' => !empty($request->is_default) ? intval($request->is_default) : 0,
              'description' => trim($request->description),
              'enable' => !empty($request->enable) ? intval($request->enable) : 1,
              'settle_ment_type' => !empty($request->settle_ment_type) ? intval($request->settle_ment_type) : 0,
              'status' => 'C',
              'creator_id' => $admin->user_id,
              'create_time' => date('Y-m-d H:i:s'),
              'name' => trim($request->name),
        ]);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        return SettleMentType::insertGetId([
              'status' => !empty($request->status) ? trim($request->status) : 'C',
              'creator_id' => $admin->user_id,
              'create_time' => date('Y-m-d H:i:s'),
              'number' => trim($request->number),
              'description' => trim($request->description),
              'is_default' => !empty($request->is_default) ? intval($request->is_default) : 0,
              'settle_ment_type' => !empty($request->settle_ment_type) ? intval($request->settle_ment_type) : 0,
              'enable' => !empty($request->enable) ? intval($request->enable) : 1,
              'status' => 'C',
              'creator_id' => $admin->user_id,
              'create_time' => date('Y-m-d H:i:s'),
              'name' => trim($request->name),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return SettleMentType::whereIn('id', $ids)->update([
              'enable' => 1,
              'modifier_id' => $admin->user_id,
              'modify_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return SettleMentType::whereIn('id', $ids)->update([
              'enable' => 0,
              'disabler_id' => $admin->user_id,
              'disable_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return SettleMentType::whereIn('id', $ids)->delete();
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('number', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->status)) {
            $status = $request->status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('status', $statusies);
        }

        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            $query->whereIn('enable', $enables);
        } else {
            $query->where('enable', 1);
        }
        if (!empty($request->settle_ment_type)) {
            $settleMentType = $request->settle_ment_type;
            $query->where('settle_ment_type', $settleMentType);
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('create_time', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('create_time', $createAts);
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setSettleMentTypes(array &$list, string $field = 'type_id', $fieldKey = 'settle_ment_name') {
        if (empty($list)) {
            return;
        }
        $settleMentIds = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = '';
            if (isset($val[$field]) && $val[$field]) {
                $settleMentIds[] = $val[$field];
            }
        }

        if (empty($settleMentIds)) {
            return $list;
        }
        $qurey = $this->model->select('id', 'name', 'number');
        $qurey->whereIn('id', $settleMentIds);
        $settleMentObjects = $qurey->get();
        if (empty($settleMentObjects)) {
            return $list;
        }

        $settleMents = $settleMentObjects->toArray();

        $settleMentArr = [];
        foreach ($settleMents as $settleMent) {
            $settleMentArr[$settleMent['id']] = $settleMent;
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($settleMentArr[$val[$field]])) {
                $val[$fieldKey] = trim($settleMentArr[$val[$field]]['name']);
            }
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setSettleMentTypeIds(array &$list, string $field = 'settle_ment_type', $fieldKey = 'settle_ment_type_id') {
        if (empty($list)) {
            return;
        }
        $settleMentNames = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = 0;
            if (isset($val[$field]) && $val[$field]) {
                $settleMentNames[] = $val[$field];
            }
        }

        if (empty($settleMentNames)) {
            return $list;
        }
        $qurey = $this->model->select('id', 'name', 'number');
        $qurey->whereIn('name', $settleMentNames);
        $settleMentObjects = $qurey->get();
        if (empty($settleMentObjects)) {
            return $list;
        }

        $settleMents = $settleMentObjects->toArray();

        $settleMentArr = [];
        foreach ($settleMents as $settleMent) {
            $settleMentArr[$settleMent['name']] = $settleMent['id'];
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($settleMentArr[$val[$field]])) {
                $val[$fieldKey] = trim($settleMentArr[$val[$field]]);
            }
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc 获取行业
     */
    public function setSettleMentType(array &$arr, string $field = 'settle_ment_type_id', $fieldKey = 'settle_ment_name') {
        if (empty($arr)) {
            return;
        }
        $groupId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $groupId = $arr[$field];
        }
        $arr[$fieldKey] = '';
        if (empty($groupId)) {
            return $arr;
        }
        $group = $this->model
          ->select('name', 'number')
          ->where('id', $groupId)
          ->first();
        if (empty($group)) {
            return;
        }
        $arr[$fieldKey] = trim($group->name);
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $query = $this->model->selectRaw('*');
        $this->getWhere($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'modifier_id', 'modifier');
        $headName = $this->getHeadName();
        $xlsName = "SettleMentType_" . date("YmdHis", time()) . uniqid(); //文件名称
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
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '结算方式');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $this->setExcelRow($sheet, strtoupper(chr($i)), 2, $head[$i - 65], 20);
        }
        $row = 3;
        foreach ($data as $item) {
            //数字转字母从65开始：
            $this->setExcelRow($sheet, 'A', $row, ' ' . $item['number'], 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['name'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $this->handelSettleMentType($item['settle_ment_type']), 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['is_default'] == 1 ? '默认' : '否', 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['modifier'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['modify_time'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['description'], 24);
            $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
          ->getSpreadsheet()
          ->getActiveSheet()
          ->getStyle('A1:H2')
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

    public function getSettleMentType($settleMentTypeName) {
        switch ($settleMentTypeName) {
            case '现金':return '1';
            case '支票':return '2';
            case '本票':return '3';
            case '汇兑':return '4';
            case '票汇':return '5';
            case '商业承兑汇票':return '6';
            case '银行承兑汇票':return '7';
            case '信用证':return '8';
            case '电子结算':return '9';
            case '承兑交单':return '10';
            case '委托收款':return '11';
            case '光票托收':return '12';
            case '远期付款交单':return '13';
            case '即期付款交单':return '14';
            case '虚拟结算':return '15';
            default: return 0;
        }
    }

    public function handelSettleMentType($settleMentType) {
        switch ($settleMentType) {
            case '1':
                return '现金';
            case '2':
                return '支票';
            case '3':
                return '本票';
            case '4':
                return '汇兑';
            case '5':
                return '票汇';
            case '6':
                return '商业承兑汇票';
            case '7':
                return '银行承兑汇票';
            case '8':
                return '信用证';
            case '9':
                return '电子结算';
            case '10':
                return '承兑交单';
            case '11':
                return '委托收款';
            case '12':
                return '光票托收';
            case '13':
                return '远期付款交单';
            case '14':
                return '即期付款交单';
            case '15':
                return '虚拟结算';
            default:
                return '';
        }
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '编码',
            '名称',
            '类别',
            '默认',
            '最后更新人',
            '最后更新时间',
            '使用状态',
            '描述',
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
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $admin = Auth::guard('admin')->user();
        $data = $this->dataTrim($importData);
        $list = [];


        foreach ($data as $v) {
            $item['number'] = trim($v[0]); //执行标准
            $item['name'] = trim($v[1]); //执行标准
            $item['settle_ment_type'] = $this->getSettleMentType(trim($v[2])); //海关编码
            $item['is_default'] = trim($v[3]) === '默认' ? '1' : 0; //质保期 
            $item['modifier_id'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['creator_id'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素     
            $item['create_time'] = date('Y-m-d H:i:s'); //申报要素          
            $item['modify_time'] = date('Y-m-d H:i:s'); //申报要素     
            $item['status'] = 'C'; //申报要素   
            $item['enable'] = trim($v[6]) === '可用' ? '1' : 0; //质保期 
            $item['description'] = trim($v[7]); //执行标准
            $list[] = $item;
        }
        return SettleMentType::upsert($list, ['number'], ['name',
              'settle_ment_type',
              'is_default',
              'description',
              'modifier_id',
              'modify_time',
              'enable']);
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

}
