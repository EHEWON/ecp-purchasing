<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Purchaser,
    UserPurchaser
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB
};
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class OrgRepo extends Repository {

    protected $model;
    protected $sorts = [
        'number',
        'name',
        'long_name',
        'enable',
        'disabled_at',
    ];

    public function __construct() {
        $this->model = new Purchaser();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'id,number,name,long_name,created_by,created_at,updated_by,updated_at,enable,purchaser_type,disabled_at') {
        $query = $this->model->selectRaw($filed);
        $this->getWhere($query, $request, ['PURCHASER', 'ORG']);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getAll(Request $request, $filed = 'id,number,name,long_name,created_by,created_at,updated_by,updated_at,enable,purchaser_type,disabled_at') {
        $query = $this->model->selectRaw($filed);
        $this->getWhere($query, $request, 'PURCHASER');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        $query->orderBy($sort, $order);
        if ($sort !== 'created_at') {
            $query->orderBy('created_at', 'DESC');
        }
    }

    public function tree(Request $request, $filed = 'id,number,name,long_name,created_by,created_at,updated_by,updated_at,parent_id,enable,purchaser_type') {
        $query = $this->model->selectRaw('id,parent_ids');
        $this->getWhere($query, $request, ['PURCHASER', 'PLATFORM', 'ORG']);
        $query->where('enable', 1);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        $ids = [];
        foreach ($list as $item) {
            $ids[] = $item['id'];
            if (!empty($item['parent_ids'])) {
                $ids = array_merge($ids, explode(',', $item['parent_ids']));
            }
        }
        $uids = array_unique($ids);
        $dquery = $this->model
                ->selectRaw($filed);
        $dquery->where('enable', 1);
        $dquery->whereIn('id', $uids);
        $this->getOrder($dquery, $request);
        $dataObject = $dquery->get();
        if (empty($dataObject)) {
            return [];
        }
        $data = $dataObject->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        return $this->handelTree($data, 0);
    }

    public function handelTree($data, $parentId) {
        if (empty($data) || $parentId) {
            return [];
        }
        $result = [];

        function children(&$result, $item) {
            foreach ($result as &$org) {
                $children = $org['children'] ?? [];
                if ($item['parent_id'] == $org['id']) {
                    $item['sort'] = empty($item['sort']) ? 0 : $item['sort'];
                    $children[] = $item;
                    $sort = [];
                    foreach ($children as $child) {
                        $sort[] = $child['sort'];
                    }
                    array_multisort($sort, SORT_DESC, SORT_NUMERIC, $children);
                    $org['children'] = $children;
                    return true;
                }
                if (!empty($org['children']) && is_array($org['children']) && count($org['children'])) {
                    $match = children($org['children'], $item);
                    if ($match) {
                        return true;
                    }
                }
            }
            return false;
        }

        foreach ($data as $key => $org) {
            if (!$org['parent_id']) {
                unset($data[$key]);
                $org['sort'] = 0;
                $result[] = $org;
            }
        }

        $count = [];

        while (($org = array_pop($data)) != NULL) {

            $count[$org['id']] = intval($count[$org['id']] ?? 0) + 1;

            $match = children($result, $org, $this->lang);

            if (!$match && $count[$org['id']] < 5) {
                array_unshift($data, $org);
            }
        }

        foreach ($result as $tp) {
            if (!empty($tp['children'])) {
                foreach ($tp['children'] as $dp) {
                    if (!empty($dp['children'])) {
                        array_multisort(array_column($dp['children'], 'sort'), SORT_DESC, SORT_NUMERIC, $dp['children']);
                    }
                }
                array_multisort(array_column($tp['children'], 'sort'), SORT_DESC, SORT_NUMERIC, $tp['children']);
            }
        }
        array_multisort(array_column($result, 'sort'), SORT_DESC, SORT_NUMERIC, $result);
        foreach ($result as $key => &$val) {
            $val['sort_flag'] = ($key + 1) . '';
            if (!empty($val['children'])) {
                foreach ($val['children'] as $ck => &$item) {
                    $item['sort_flag'] = $val['sort_flag'] . ',' . ($ck + 1);
                    if (!empty($item['children'])) {
                        foreach ($item['children'] as $cck => &$v) {
                            $v['sort_flag'] = $item['sort_flag'] . ',' . ($cck + 1);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {

        $query = $this->model->selectRaw('id,number,name,long_name,'
                . 'created_by,created_at,updated_by,updated_at,parent_id,parent_ids,`describe`');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $data['parent_ids'] = explode(',', $data['parent_ids']);
        (new UserRepo)->setUser($data, 'created_by', 'created_name');
        (new UserRepo)->setUser($data, 'updated_by', 'updated_name');
        $this->setOrg($data, 'parent_id', 'parent_name');
        return $data;
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function edited($id, Request $request) {
        $purchaser = Purchaser::select('id', 'long_name', 'parent_ids', 'bottom_id')
                ->where('id', $request->parent_id)
                ->first();
        $parentIds = !empty($purchaser) ? $purchaser->parent_ids : '';
        $longName = !empty($purchaser) ? $purchaser->long_name : '';
        $bottomId = !empty($purchaser) ? $purchaser->bottom_id : '';
        $admin = Auth::guard('admin')->user();
        $flag = Purchaser::where('id', $id)->update([
//            'purchaser_type' => 'ORG',
            'enable' => !empty($request->enable) ? intval($request->enable) : 1,
            'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
            'updated_at' => date('Y-m-d H:i:s'),
            'parent_id' => !empty($request->parent_id) ? intval($request->parent_id) : 0,
            'bottom_id' => $bottomId,
            'long_name' => ltrim($longName . '_' . trim($request->name), '_'),
            'parent_ids' => !empty($request->parent_id) ? ltrim($parentIds . ',' . intval($request->parent_id), ',') : '',
            'name' => trim($request->name),
            'describe' => !empty($request->describe) ? trim($request->describe) : '',
            'province_id' => 0,
            'city_id' => 0,
        ]);
        UserPurchaser::where('purchaser_id', $id)->update(['bot_purchaser_id' => $bottomId]);
        return $flag;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        $purchaser = Purchaser::select('id', 'long_name', 'parent_ids', 'bottom_id')
                ->where('id', $request->parent_id)
                ->first();
        $parentIds = !empty($purchaser) ? $purchaser->parent_ids : '';
        $longName = !empty($purchaser) ? $purchaser->long_name : '';
        $bottomId = !empty($purchaser) ? $purchaser->bottom_id : '';
        return Purchaser::insertGetId([
                    'purchaser_type' => 'ORG',
                    'number' => !empty(trim($request->number)) ? trim($request->number) : $this->getOrgNo(),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'created_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'long_name' => ltrim($longName . '_' . trim($request->name), '_'),
                    'parent_id' => !empty($request->parent_id) ? intval($request->parent_id) : 0,
                    'parent_ids' => !empty($request->parent_id) ? ltrim($parentIds . ',' . intval($request->parent_id), ',') : '',
                    'bottom_id' => $bottomId,
                    'name' => trim($request->name),
                    'long_name' => trim($longName) . '_' . trim($request->name),
                    'contact_name' => '',
                    'contact_phone' => '',
                    'contact_email' => '',
                    'status' => 'APPROVED',
                    'describe' => !empty($request->describe) ? trim($request->describe) : '',
                    'province_id' => 0,
                    'city_id' => 0,
                    'contact_address' => '',
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        if ($request->type === 'ALL') {
            return Purchaser::where('enable', 0)
//                ->where('purchaser_type', 'ORG')
                            ->update([
                                'enable' => 1,
                                'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'disabled_at' => null,
            ]);
        } else {
            $ids = $request->ids;
            return Purchaser::whereIn('id', $ids)
                            ->where('enable', 0)
//                ->where('purchaser_type', 'ORG')
                            ->update([
                                'enable' => 1,
                                'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'updated_at' => date('Y-m-d H:i:s'),
                                'disabled_at' => null,
            ]);
        }
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        if ($request->type === 'ALL') {
            return Purchaser::where('enable', 0)
//                ->where('purchaser_type', 'ORG')
                            ->update([
                                'enable' => 1,
                                'disabled_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'disabled_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $ids = $request->ids;
            return Purchaser::whereIn('id', $ids)
                            ->where('enable', 1)
//                ->where('purchaser_type', 'ORG')
                            ->update([
                                'enable' => 0,
                                'disabled_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'disabled_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Purchaser:: where('purchaser_type', 'ORG')->whereIn('id', $ids)->update(['deleted_flag' => 'Y']);
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */

    /**
     * @param $query
     * @param Request $request
     * @param string $purchaserType
     */
    protected function getWhere(&$query, Request $request, $purchaserType = 'ORG') {
        $query->where('deleted_flag', 'N');
        if (empty($purchaserType)) {
            $query->where('purchaser_type', 'ORG');
        } elseif (is_string($purchaserType)) {
            $query->where('purchaser_type', $purchaserType);
        } elseif (is_array($purchaserType)) {
            $query->whereIn('purchaser_type', $purchaserType);
        }
        if (!empty($request->purchaser_type)) {
            $query->where('purchaser_type', $request->purchaser_type);
        }
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('number', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($request->country_id)) {
            $countryId = trim($request->country_id);
            $countryIds = explode(',', $countryId);
            $query->whereIn('country_id', $countryIds);
        }
        if (!empty($request->number)) {
            $query->where('number', 'like', '%' . $request->number . '%');
        }
        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if (!empty($request->long_name)) {
            $query->where('long_name', 'like', '%' . $request->long_name . '%');
        }
        if (!empty($request->parent_id)) {
            $parentId = intval($request->parent_id);
            $query->where(function($q)use($parentId) {
                $q->where('id', $parentId)
                        ->orWhereRaw('FIND_IN_SET(' . $parentId . ',parent_ids) ');
            });
        }

        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            foreach ($enables as &$enable) {
                switch ($enable) {
                    case '2':
                        $enable = 0;
                        break;
                    case '1':
                        $enable = 1;
                        break;
                }
            }
            $query->whereIn('enable', $enables);
        }

        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('created_at', $createAts);
        } elseif (!empty($request->created_at)) {
            $createtime = trim($request->created_at);
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('created_at', $createAts);
        }

        if (!empty($request->disabletype)) {
            $createAts = $this->getTimeByType($request->disabletype);
            $query->whereBetween('disabled_at', $createAts);
        } elseif (!empty($request->disabled_at)) {
            $disabledAt = trim($request->disabled_at);
            $disabledAts = is_array($disabledAt) ? $disabledAt : explode(',', $disabledAt);
            !empty($disabledAts[1]) ? $disabledAts[1] = date('Y-m-d 23:59:59', strtotime($disabledAts[1])) : $disabledAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('disabled_at', $disabledAts);
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
    public function setOrg(array &$data, string $field = 'purchaser_id', $fieldKey = 'purchaser_name') {
        if (empty($data)) {
            return;
        }
        $data[$fieldKey] = '';
        $purchaserId = $data[$field];
        if (empty($purchaserId)) {
            return $data;
        }
        $qurey = $this->model
                ->select('id', 'name', 'long_name');
        $qurey->where('id', $purchaserId);
        $purchaserObject = $qurey->first();
        if (empty($purchaserObject)) {
            return $data;
        }
        $purchaser = $purchaserObject->toArray();
        $data[$fieldKey] = $purchaser['name'];
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setOrgs(array &$list, string $field = 'purchaser_id', $fieldKey = 'purchaser_name', $longFlag = false) {
        if (empty($list)) {
            return;
        }
        $purchaserIds = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = '';
            if (isset($val[$field]) && $val[$field]) {
                $purchaserIds[] = $val[$field];
            }
        }

        if (empty($purchaserIds)) {
            return $list;
        }
        $qurey = $this->model
                ->select('id', 'name', 'long_name');
        $qurey->whereIn('id', $purchaserIds);

        $purchaserObjects = $qurey->get();
        if (empty($purchaserObjects)) {
            return $list;
        }
        $purchasers = $purchaserObjects->toArray();
        $longPurchaserArr = $purchaserArr = [];
        foreach ($purchasers as $purchaser) {
            $purchaserArr[$purchaser['id']] = $purchaser['name'];
            $longPurchaserArr[$purchaser['id']] = $purchaser['long_name'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($purchaserArr[$val[$field]])) {
                $val[$fieldKey] = $purchaserArr[$val[$field]];
            }
            if ($longFlag && isset($val[$field]) && isset($longPurchaserArr[$val[$field]])) {
                $val['long_name'] = $longPurchaserArr[$val[$field]];
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
        if ($request->type === 'ALL') {
            $query->where('purchaser_type', 'ORG')
                    ->where('deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('purchaser_type', 'ORG')
                    ->where('deleted_flag', 'N')
                    ->whereIn('id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $object = $query->orderBy('created_at', 'DESC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        $this->setOrgs($data, 'parent_id', 'parent_name');
        $headName = $this->getHeadName();
        $xlsName = "ORG_" . date("YmdHis", time()) . uniqid(); //文件名称
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
        $sheet->setCellValue('A1', '组织机构');
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
            $this->setExcelRow($sheet, 'C', $row, $item['parent_name'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['long_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['describe'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, $item['created_name'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, $item['created_at'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
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

    public function getPrentId($parentName) {
        if (empty($parentName)) {
            return 0;
        }
        return $this->model->where('name', $parentName)
                        ->where('deleted_flag', 'N')
                        ->value('id');
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
            '上级组织',
            '长名称',
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
        $newNumber = null;
        foreach ($data as $v) {
            $item['number'] = !empty(trim($v[0])) ? trim($v[0]) : $this->getOrgNo($newNumber); //执行标准
            $item['purchaser_type'] = 'ORG'; //执行标准
            $item['name'] = trim($v[1]); //执行标准
            $item['parent_id'] = $this->getPrentId(trim($v[2])); //质保期
            $item['status'] = 'APPROVED';
            $item['long_name'] = trim($v[3]); //海关编码
            $item['describe'] = trim($v[4]); //海关编码
            $item['created_by'] = $admin->user_id; //申报要素     
            $item['created_at'] = date('Y-m-d H:i:s'); //申报要素    
            $item['updated_by'] = $admin->user_id; //申报要素
            $item['updated_at'] = date('Y-m-d H:i:s'); //申报要素     
            $item['enable'] = trim($v[7]) === '可用' ? '1' : 0; //质保期    //申报要素    
            $list[] = $item;
        }

        return Purchaser::upsert($list, ['number'], ['name',
                    'parent_id',
                    'long_name',
                    'describe',
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
    public function getOrgNo(&$newNumber = null) {
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

    public function getCurPurChaserOrgIds($userId, $curId = null) {
        if (!empty($curId)) {
            return Purchaser::where('deleted_flag', 'N')
                            ->where('purchaser_type', 'Org')
                            ->whereRaw('FIND_IN_SET(' . $curId . ',parent_ids) ')
                            ->pluck('id');
        }
        $purchaser = (new Purchaser)->getTable();
        $userPurchaser = (new UserPurchaser)->getTable();
        $defaultObj = Purchaser::selectRaw('up.purchaser_id,p.parent_ids')
                ->from($purchaser . ' as p')
                ->join($userPurchaser . ' as up', function($join) {
                    $join->on('p.id', '=', 'up.purchaser_id');
                })
                ->where('up.user_id', $userId)
                ->where('up.is_default', 1)
                ->where('up.deleted_flag', 'N')
                ->where('p.deleted_flag', 'N')
                ->first();
        if (empty($defaultObj)) {
            return [];
        }
        $parentIdArr = explode(',', $defaultObj->parent_ids);
        $parentIdArr[] = $defaultObj->purchaser_id;
        $defaultId = Purchaser::whereIn('id', $parentIdArr)
                ->where('deleted_flag', 'N')
                ->where('purchaser_type', 'PURCHASER')
                ->orderBy(DB::Raw('LENGTH(parent_ids)'), 'DESC')
                ->value('id');
        if (empty($defaultId)) {
            return [];
        }
        return Purchaser::where('deleted_flag', 'N')
                        ->where('purchaser_type', 'Org')
                        ->whereRaw('FIND_IN_SET(' . $defaultId . ',parent_ids) ')
                        ->pluck('id');
    }

    /**
     * 获取第一级公司ID
     * @param type $purchaserId
     * @return type
     */
    public function getPurchaserId($purchaserId) {
        $obj = Purchaser::selectRaw('id,parent_ids')
                ->where('deleted_flag', 'N')
                ->where('id', $purchaserId)
                ->first();
        if (empty($obj)) {
            return 0;
        }
        $parentIdArr = explode(',', $obj->parent_ids);
        $parentIdArr[] = $obj->id;
        $defaultId = Purchaser::whereIn('id', $parentIdArr)
                ->where('deleted_flag', 'N')
                ->where('purchaser_type', 'PURCHASER')
                ->orderBy(DB::Raw('LENGTH(parent_ids)'), 'DESC')
                ->value('id');
        if (empty($defaultId)) {
            return 0;
        }
        return $defaultId;
    }

    /**
     * 获取第一级公司ID
     * @param type $purchaserId
     * @return type
     */
    public function getBottomId($purchaserId) {
        $purchaser = Purchaser::selectRaw('id,parent_ids,bottom_id')
                ->where('deleted_flag', 'N')
                ->where('id', $purchaserId)
                ->first();
        if (empty($purchaser)) {
            return 0;
        }
        return $purchaser->bottom_id;
    }

}
