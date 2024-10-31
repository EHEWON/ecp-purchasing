<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    SupplierGrade,
    SupplierGradentry
};
use Illuminate\Http\Request;
use App\Common\Helpers\Excel;
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};
use App\Modules\Admin\Repository\SupplierGradentryRepo;
use Illuminate\Support\Facades\Auth;

class SupplierGradeRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierGrade();
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
        $showPage = !empty($request->show_page) && $request->show_page == 'N' ? false : true;
        $clone = $query->clone();
        $this->getPage($query, $request);
        $total = $clone->count();
        $object = $query->orderBy('create_time', 'DESC')->get();
        if (empty($object)) {
            return $showPage ? ['data' => [], 'total' => $total] : [];
        }
        $list = [];
        $list['total'] = $total;
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['status_text'] = $this->getStatusText($item['status']);
        }
        (new UserRepo)->setUsers($data, 'modifier_id', 'modifier');
        (new SupplierGradentryRepo)->setGradentrys($data, 'id');
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
        $data['status_text'] = $this->getStatusText($data['status']);
        (new UserRepo)->setUser($data, 'modifier_id', 'modifier');
        (new SupplierGradentryRepo)->setGradentry($data, 'id');
        return $data;
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function edited($gradeId, Request $request) {
        $admin = Auth::guard('admin')->user();
        $flag = SupplierGrade::where('id', $gradeId)->update([
            'status' => !empty($request->status) ? trim($request->status) : 'A',
            'modifier_id' => $admin->user_id,
            'modify_time' => date('Y-m-d H:i:s'),
            'remark' => !empty($request->remark) ? trim($request->remark) : '',
            'enable' => !empty($request->enable) ? intval($request->enable) : 1,
            'status' => 'C',
            'creator_id' => $admin->user_id,
            'create_time' => date('Y-m-d H:i:s'),
            'name' => trim($request->name),
        ]);

        if (!empty($request->gradentry)) {
            $dataList = [];
            $gradentryIds = [];
            foreach ($request->gradentry as $key => $gradentry) {
                !empty($gradentry['id']) ? $gradentryIds[] = $gradentry['id'] : null;
                $dataList[] = [
                    'id' => !empty($gradentry['id']) ? $gradentry['id'] : null,
                    'grade_id' => $gradeId,
                    'eva_grade_id' => !empty($gradentry['eva_grade_id']) ? intval($gradentry['eva_grade_id']) : 0,
                    'seq' => $key + 1,
                    'score_from' => !empty($gradentry['score_from']) ? floatval($gradentry['score_from']) : 0,
                    'score_to' => !empty($gradentry['score_to']) ? floatval($gradentry['score_to']) : 0,
                    'note' => !empty($gradentry['note']) ? trim($gradentry['note']) : '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            !empty($gradentryIds) ? SupplierGradentry::where('grade_id', $gradeId)
                                    ->whereIn('id', $gradentryIds)
                                    ->delete() : SupplierGradentry::where('grade_id', $gradeId)
                                    ->delete();
            SupplierGradentry::insert($dataList, ['id'], ['eva_grade_id', 'seq', 'score_from', 'score_to', 'note', 'updated_at']);
        } else {
            SupplierGradentry::where('grade_id', $gradeId)->delete();
        }
        return $flag;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        $gradeId = SupplierGrade::insertGetId([
                    'status' => !empty($request->status) ? trim($request->status) : 'A',
                    'remark' => !empty($request->remark) ? trim($request->remark) : '',
                    'creator_id' => $admin->user_id,
                    'create_time' => date('Y-m-d H:i:s'),
                    'number' => trim($request->number),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'status' => 'C',
                    'name' => trim($request->name),
        ]);
        if (!empty($request->gradentry)) {
            $dataList = [];
            foreach ($request->gradentry as $key => $gradentry) {
                $dataList[] = [
                    'grade_id' => $gradeId,
                    'eva_grade_id' => !empty($gradentry->eva_grade_id) ? intval($gradentry->eva_grade_id) : 0,
                    'seq' => $key + 1,
                    'score_from' => !empty($request->score_from) ? floatval($request->score_from) : null,
                    'score_to' => !empty($request->score_to) ? floatval($request->score_to) : null,
                    'note' => !empty($request->remark) ? trim($request->remark) : '',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            SupplierGradentry::insert($dataList);
        }
        return $gradeId;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return SupplierGrade::whereIn('id', $ids)->update([
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
        return SupplierGrade::whereIn('id', $ids)->update([
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
        return SupplierGrade::whereIn('id', $ids)->delete();
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
    public function setGroups(array &$list, string $field = 'group_id') {
        if (empty($list)) {
            return;
        }
        $groupIds = [];
        foreach ($list as &$val) {
            $val['group'] = '';
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
                $val['group'] = $groupArr[$val[$field]];
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
    public function setGroupIds(array &$list, string $field = 'group') {
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
    public function setGroup(array &$arr, string $field = 'group_id') {
        if (empty($arr)) {
            return;
        }
        $groupId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $groupId = $arr[$field];
        }

        $arr['group'] = '';
        if (empty($groupId)) {
            return $arr;
        }
        $group = $this->model
                ->select('name', 'number')
                ->where('id', $groupId)
                ->first();
        $arr['group'] = !empty(trim($group->name)) ? trim($group->name) : trim($group->number);
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
        foreach ($data as &$item) {
            $item['status_text'] = $this->getStatusText($item['status']);
        }
        $headName = $this->getHeadName();
        $xlsName = "SupplierGrade_" . date("YmdHis", time()) . uniqid(); //文件名称
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
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '分级方案');
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
            $this->setExcelRow($sheet, 'C', $row, $item['remark'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['status_text'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['modifier'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['modify_time'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $row++;
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

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '方案编码',
            '方案名称',
            '方案描述',
            '数据状态',
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
            $item['remark'] = trim($v[2]); //执行标准    
            $item['creator_id'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['modifier_id'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素     
            $item['create_time'] = date('Y-m-d H:i:s'); //申报要素          
            $item['modify_time'] = date('Y-m-d H:i:s'); //申报要素     
            $item['status'] = 'A'; //申报要素   
            $item['enable'] = trim($v[6]) === '可用' ? '1' : 0; //质保期 

            $list[] = $item;
        }
        return SupplierGrade::upsert($list, ['number'], ['name',
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

    public function getStatusText($status) {
        switch (strtoupper($status)) {
            case 'A':
                return '保存';
            case 'B':
                return '已提交';
            case 'C':
                return '已审核';
            default : return '其他';
        }
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getSupplierGradeNo(&$newNumber = null) {
        $prefix = 'FJ';
        $qurey = $this->model->selectRaw('*');
        $number = $newNumber ? $newNumber : $qurey
                        ->where('number', 'like', $prefix . '%')
                        ->orderBy('number', 'DESC')
                        ->value('number');
        if (!empty($number)) {
            $serialSetp = substr($number, 2, 4);
            $step = intval($serialSetp);
            $step ++;
            $newNumber = $this->createSerialNo($step, $prefix);
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
    private function createSerialNo($step = 1, $prefix = '') {
        $pad = str_pad($step, 4, '0', STR_PAD_LEFT);
        return$prefix . $pad;
    }

}
