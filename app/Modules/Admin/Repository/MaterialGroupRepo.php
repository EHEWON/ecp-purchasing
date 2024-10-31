<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\MaterialGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class MaterialGroupRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new MaterialGroup();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = '*') {
        $query = $this->model
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $object = $query->orderBy('created_at', 'DESC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        $this->setGroups($list, 'parent_id', ['parent_name' => 'name', 'parent_number' => 'number']);
        (new UserRepo)->setUsers($list, 'updated_by', 'updated_name');
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {

        $query = $this->model->selectRaw('*');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $this->setGroup($data, 'parent_id', ['parent_name' => 'name', 'parent_number' => 'number']);
        (new UserRepo)->setUser($data, 'updated_by', 'updated_name');
        return $data;
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function edited($id, Request $request) {
        $parent = [];
        if (!empty($request->parent_id)) {
            $parentObj = MaterialGroup::select('is_leaf', 'level', 'number')
                    ->where('id', $request->parent_id)
                    ->first();
            if (!empty($parentObj)) {
                $parent = $parentObj->toArray();
            }
        }
        $admin = Auth::guard('admin')->user();
        return MaterialGroup::where('id', $id)->update([
                    'status' => !empty($request->status) ? trim($request->status) : 'C',
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'description' => !empty($request->description) ? trim($request->description) : '',
                    'number' => trim($request->number),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'parent_id' => !empty($request->parent_id) ? intval($request->parent_id) : 0,
                    'level' => !empty($parent['level']) ? intval($parent['level']) + 1 : 1,
                    'is_leaf' => !empty($request->parent_id) ? 1 : 0,
                    'long_number' => !empty($parent['number']) ? trim($parent['number']) . '!' . trim($request->number) : trim($parent['number']),
                    'status' => 'C',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
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
        $parent = [];
        if (!empty($request->parent_id)) {
            $parentObj = MaterialGroup::select('is_leaf', 'level', 'number')
                    ->where('id', $request->parent_id)
                    ->first()
            ;
            if (!empty($parentObj)) {
                $parent = $parentObj->toArray();
            }
        }
        return MaterialGroup::insertGetId([
                    'status' => !empty($request->status) ? trim($request->status) : 'C',
                    'description' => !empty($request->description) ? trim($request->description) : '',
                    'parent_id' => !empty($request->parent_id) ? intval($request->parent_id) : 0,
                    'level' => !empty($parent['level']) ? intval($parent['level']) + 1 : 1,
                    'is_leaf' => !empty($request->parent_id) ? 1 : 0,
                    'long_number' => !empty($parent['number']) ? trim($parent['number']) . '!' . trim($request->number) : trim($parent['number']),
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'number' => trim($request->number),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'status' => 'C',
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
        return MaterialGroup::whereIn('id', $ids)->update([
                    'enable' => 1,
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
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
        return MaterialGroup::whereIn('id', $ids)->update([
                    'enable' => 0,
                    'disabled_by' => $admin->user_id,
                    'disabled_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return MaterialGroup::whereIn('id', $ids)->delete();
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
        if (!empty($request->parent_id)) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->where('parent_id', 0);
        }
        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            $query->whereIn('enable', $enables);
        } else {
            $query->where('enable', 1);
        }

        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('created_at', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('created_at', $createAts);
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
    public function setGroups(array &$list, string $field = 'group_id', $fieldKeys = ['group_name' => 'name', 'group_number' => 'number']) {
        if (empty($list)) {
            return;
        }
        $groupIds = [];
        foreach ($list as &$val) {
            foreach ($fieldKeys as $filedKey => $column) {
                $val[$filedKey] = '';
            }
            if (isset($val[$field]) && $val[$field]) {
                $groupIds[] = $val[$field];
            }
        }

        if (empty($groupIds)) {
            return $list;
        }
        $fields = 'id';
        foreach ($fieldKeys as $fieldKey => $column) {
            $fields .= ',' . $column;
        }
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('id', $groupIds);
        $groupObjects = $qurey->get();
        if (empty($groupObjects)) {
            return $list;
        }

        $groups = $groupObjects->toArray();

        $groupArr = [];
        foreach ($groups as $group) {
            $groupArr[$group['id']] = $group;
        }

        foreach ($list as &$val) {
            foreach ($fieldKeys as $filedKey => $colunm) {
                if ($val[$field] && isset($groupArr[$val[$field]][$colunm])) {
                    $val[$filedKey] = $groupArr[$val[$field]][$colunm];
                } else {
                    $val[$filedKey] = '';
                }
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
    public function setGroupIds(array &$list, string $field = 'group', $fieldKeys = ['group_id' => 'id', 'group_number' => 'number']) {
        if (empty($list)) {
            return;
        }
        $groupNames = [];
        foreach ($list as &$val) {
            foreach ($fieldKeys as $fieldKey => $column) {
                $val[$fieldKey] = '';
            }
            if (isset($val[$field]) && $val[$field]) {
                $groupNames[] = $val[$field];
            }
        }

        if (empty($groupNames)) {
            return $list;
        }
        $fields = 'name';
        foreach ($fieldKeys as $fieldKey => $column) {
            $fields .= ',' . $column;
        }
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('name', $groupNames);
        $groupObjects = $qurey->get();
        if (empty($groupObjects)) {
            return $list;
        }
        $groups = $groupObjects->toArray();
        $groupArr = [];
        foreach ($groups as $group) {
            $groupArr[$group['name']] = $group;
        }

        foreach ($list as &$val) {
            foreach ($fieldKeys as $fieldKey => $colunm) {
                if ($val[$field] && isset($groupArr[$val[$field]][$colunm])) {
                    $val[$fieldKey] = $groupArr[$val[$field]][$colunm];
                } else {
                    $val[$fieldKey] = '';
                }
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
    public function setGroup(array &$arr, string $field = 'group_id', $fieldKeys = ['group_id' => 'id', 'group_number' => 'number']) {
        if (empty($arr)) {
            return;
        }
        $groupId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $groupId = $arr[$field];
        }
        foreach ($fieldKeys as $fieldKey => $colunm) {
            $arr[$fieldKey] = '';
        }
        if (empty($groupId)) {
            return $arr;
        }
        $group = $this->model
                ->select('name', 'number')
                ->where('id', $groupId)
                ->first()
                ->toArray();
        foreach ($fieldKeys as $fieldKey => $colunm) {
            if (isset($group[$arr[$field]][$colunm])) {
                $arr[$fieldKey] = $group[$arr[$field]][$colunm];
            } else {
                $arr[$fieldKey] = '';
            }
        }
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
        (new UserRepo)->setUsers($data, 'updated_by', 'modifier');
        $this->setGroups($data, 'parent_id', ['parent_name' => 'name', 'parent_number' => 'number']);
        $headName = $this->getHeadName();
        $xlsName = "MaterialGroup_" . date("YmdHis", time()) . uniqid(); //文件名称
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
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '物料分类');
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
            $this->setExcelRow($sheet, 'C', $row, $item['parent_number'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['parent_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['is_leaf'] == 1 ? '是' : '否', 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['level'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['description'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['modifier'], 24);
            $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'I', $row, $item['updated_at'], 24);
            $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'J', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
            $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:J2')
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
            '编码',
            '名称',
            '上级分类.分类编码',
            '上级分类.分类名称',
            '是否叶子',
            '级次',
            '描述',
            '最后更新人',
            '最后更新时间',
            '使用状态',
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
            $item['parent_name'] = trim($v[3]); //执行标准
            $item['is_leaf'] = trim($v[4]) === '是' ? '1' : 0; //质保期 
            $item['level'] = intval($v[5]); //执行标准
            $item['description'] = trim($v[6]); //执行标准
            $item['created_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['updated_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素 
            $item['created_at'] = date('Y-m-d H:i:s'); //申报要素          
            $item['updated_at'] = date('Y-m-d H:i:s'); //申报要素
            $item['status'] = 'C'; //申报要素   
            $item['enable'] = trim($v[9]) === '可用' ? '1' : 0; //质保期 
            $list[] = $item;
        }
        $this->setGroupIds($list, 'parent_name', ['parent_id' => 'id', 'parent_lnumber' => 'long_number', 'parent_level' => 'level']);
        foreach ($list as &$item) {
            unset($item['parent_name']);
            $item['long_number'] = $item['parent_lnumber'] . '!' . $item['number']; //执行标准
            $item['level'] = $item['parent_level'] + 1; //执行标准
            unset($item['parent_name'], $item['parent_level'], $item['parent_lnumber']);
        }
        return MaterialGroup::upsert($list, ['number'], ['name',
                    'updated_by',
                    'updated_at',
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

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getMaterialGroupNo(&$newNumber = null) {
        $prefix = 'RUI-';
        $qurey = $this->model->selectRaw('*');
        $number = $newNumber ? $newNumber : $qurey
                        ->where('number', 'like', $prefix . '%')
                        ->orderBy('number', 'DESC')
                        ->value('number');
        if (!empty($number)) {
            $date = substr($number, 4, 8);
            $serialSetp = substr($number, 14, 5);
            $step = intval($serialSetp);
            $step ++;
            $newNumber = $this->createSerialNo($step, $prefix, $date);
            return $newNumber;
        }
        $newNumber = $this->createSerialNo(1, $prefix, '');
        return $newNumber;
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

}
