<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\SupplierGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class SupplierGroupRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierGroup();
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
        $showPage = !empty($request->show_page) && $request->show_page == 'Y' ? true : false;
        $clone = $query->clone();
        $this->getPage($query, $request);
        $total = $clone->count();
        $object = $query->orderBy('created_at', 'ASC')->get();
        if (empty($object)) {
            return $showPage ? ['data' => [], 'total' => $total] : [];
        }
        $list = [];
        $list['total'] = $total;
        $data = $object->toArray();
        $list['data'] = $data;
        return $showPage ? $list : $data;
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
        return SupplierGroup::where('id', $id)->update([
                    'status' => !empty($request->status) ? trim($request->status) : 'C',
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'description' => !empty($request->description) ? trim($request->description) : '',
                    'number' => trim($request->number),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
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
        return SupplierGroup::insertGetId([
                    'status' => !empty($request->status) ? trim($request->status) : 'C',
                    'description' => !empty($request->description) ? trim($request->description) : '',
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
        return SupplierGroup::whereIn('id', $ids)->update([
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
        return SupplierGroup::whereIn('id', $ids)->update([
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
        return SupplierGroup::whereIn('id', $ids)->delete();
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
    public function setGroups(array &$list, string $field = 'supplier_group_id', $fieldKey = 'supplier_group_name') {
        if (empty($list)) {
            return;
        }
        $groupIds = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = '';
            if (isset($val[$field]) && $val[$field]) {
                $groupIds[] = $val[$field];
            }
        }

        if (empty($groupIds)) {
            return $list;
        }
        $qurey = $this->model->select('id', 'name', 'number');
        $qurey->whereIn('id', $groupIds);
        $groupObjects = $qurey->get();
        if (empty($groupObjects)) {
            return $list;
        }

        $groups = $groupObjects->toArray();

        $groupArr = [];
        foreach ($groups as $group) {
            $groupArr[$group['id']] = !empty(trim($group['name'])) ? trim($group['name']) : trim($group['number']);
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($groupArr[$val[$field]])) {
                $val[$fieldKey] = $groupArr[$val[$field]];
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
    public function setGroupIds(array &$list, string $field = 'supplier_group_id', $fieldKey = 'supplier_group_name') {
        if (empty($list)) {
            return;
        }
        $groupNames = [];
        foreach ($list as &$val) {
            $val['group_id'] = 0;
            if (isset($val[$field]) && $val[$field]) {
                $groupNames[] = $val[$field];
            }
        }
        if (empty($groupNames)) {
            return $list;
        }
        $qurey = $this->model->select('id', 'name', 'number');
        $qurey->whereIn('name', $groupNames);
        $groupObjects = $qurey->get();
        if (empty($groupObjects)) {
            return $list;
        }

        $groups = $groupObjects->toArray();

        $groupArr = [];
        foreach ($groups as $group) {
            $groupArr[$group['name']] = $group['id'];
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($groupArr[$val[$field]])) {
                $val['group_id'] = $groupArr[$val[$field]];
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
    public function setGroup(array &$arr, string $field = 'supplier_group_id', $fieldKey = 'supplier_group_name') {
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
        $arr[$fieldKey] = !empty(trim($group->name)) ? trim($group->name) : trim($group->number);
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
        $headName = $this->getHeadName();
        $xlsName = "SupplierGroup_" . date("YmdHis", time()) . uniqid(); //文件名称
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
        $sheet->mergeCells('A1:E1');
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

            $this->setExcelRow($sheet, 'C', $row, $item['modifier'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['updated_at'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:E2')
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
            $item['created_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['updated_by'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素     
            $item['created_at'] = date('Y-m-d H:i:s'); //申报要素          
            $item['updated_at'] = date('Y-m-d H:i:s'); //申报要素     
            $item['status'] = 'C'; //申报要素   
            $item['enable'] = trim($v[4]) === '可用' ? '1' : 0; //质保期 

            $list[] = $item;
        }
        return SupplierGroup::upsert($list, ['number'], ['name',
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

}
