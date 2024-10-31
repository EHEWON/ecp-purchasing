<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Template;
use App\Modules\Admin\Repository\{
    TemplateTypeRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Template();
        parent::__construct($this->model);
    }

    public function getAll(Request $request) {
        $fields = 'id,name,number';
        $qurey = $this->model->selectRaw($fields);
        $qurey->where('enable', '1');
        $this->getWhere($qurey, $request);
        return $qurey->get();
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setTemplates(array &$list, $field = 'template_id', $fieldKey = 'template_name') {
        if (empty($list)) {
            return;
        }
        $TemplateIds = [];
        foreach ($list as &$val) {
            if (isset($val[$field]) && $val[$field]) {
                $TemplateIds[] = $val[$field];
            }
            $val[$fieldKey] = '';
        }
        if (empty($TemplateIds)) {
            return $list;
        }
        $fields = 'id,name';
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('id', $TemplateIds)
                ->where('enable', '1');
        $TemplateObjects = $qurey->get();
        if (empty($TemplateObjects)) {
            return $list;
        }
        $Templates = $TemplateObjects->toArray();
        $TemplateArr = [];
        foreach ($Templates as $Template) {
            $TemplateArr[$Template['id']] = $Template['name'];
        }

        foreach ($list as &$val) {
            if ($val[$field] && isset($TemplateArr[$val[$field]])) {
                $val[$fieldKey] = $TemplateArr[$val[$field]];
            } else {
                $val[$fieldKey] = '';
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
    public function setTemplate(array &$arr, string $field = 'template_id', $fieldKey = 'template_name') {
        if (empty($arr)) {
            return;
        }
        $TemplateId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $TemplateId = $arr[$field];
        }
        $fields = 'id,name';
        if (empty($TemplateId)) {
            return $arr;
        }

        $Template = $this->model
                ->selectRaw($fields)
                ->where('id', $TemplateId)
                ->where('enable', '1')
                ->first();

        $arr[$fieldKey] = !empty($Template) ? $Template->name : '';
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
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $object = $query->orderBy('created_at', 'DESC')->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new TemplateTypeRepo)->setTemplateTypes($data, 'type_id', 'type_name');

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

        $query = $this->model->selectRaw('*');
        $query->where('id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUser($data, 'created_by', 'created_name');
        (new TemplateTypeRepo)->setTemplateType($data, 'type_id', 'type_name');
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
        return Template::where('id', $id)->update([
                    'number' => trim($request->number),
                    'status' => !empty($request->status) ? trim($request->status) : 'C',
                    'org_id' => $this->getPPurchaserId(),
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'status' => 'C',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'name' => trim($request->name),
                    'type_id' => !empty($request->type_id) ? intval($request->type_id) : 0,
                    'content' => !empty($request->content) ? intval($request->content) : 0,
        ]);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        return Template::insertGetId([
                    'number' => trim($request->number),
                    'status' => !empty($request->status) ? trim($request->status) : 'C',
                    'updated_by' => $admin->user_id,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'status' => 'C',
                    'org_id' => $this->getPPurchaserId(),
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'name' => trim($request->name),
                    'type_id' => !empty($request->type_id) ? intval($request->type_id) : 0,
                    'content' => !empty($request->content) ? intval($request->content) : 0,
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
        return Template::whereIn('id', $ids)->update([
                    'enable' => 1,
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
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
        return Template::whereIn('id', $ids)->update([
                    'enable' => 0,
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Template::whereIn('id', $ids)->delete();
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
        if (!empty($request->org_id)) {
            $query->where('org_id', trim($request->org_id));
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
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getTemplateNo(&$newNumber = null) {
        $prefix = 'HJMB';
        $qurey = $this->model->selectRaw('*');
        $number = $newNumber ? $newNumber : $qurey
                        ->where('number', 'like', $prefix . '%')
                        ->orderBy('number', 'DESC')
                        ->value('number');
        if (!empty($number)) {
            $date = substr($number, 4, 8);
            $serialSetp = substr($number, 12, 5);
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
