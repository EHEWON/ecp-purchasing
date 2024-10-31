<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\ProjectTemplateType;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;

class TemplateTypeRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectTemplateType();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getAll(Request $request, $filed = '*') {
        $query = $this->model
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $object = $query->orderBy('created_at', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        return $data;
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
    public function setTemplateTypes(array &$list, $field = 'pur_type_id', $fieldKey = 'pur_type_name') {
        if (empty($list)) {
            return;
        }
        $TemplateTypeIds = [];
        foreach ($list as &$val) {
            if (isset($val[$field]) && $val[$field]) {
                $TemplateTypeIds[] = $val[$field];
            }
            $val[$fieldKey] = '';
        }
        if (empty($TemplateTypeIds)) {
            return $list;
        }
        $fields = 'id,name';
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('id', $TemplateTypeIds)
                ->where('enable', '1');
        $TemplateTypeObjects = $qurey->get();
        if (empty($TemplateTypeObjects)) {
            return $list;
        }
        $TemplateTypes = $TemplateTypeObjects->toArray();
        $TemplateTypeArr = [];
        foreach ($TemplateTypes as $TemplateType) {
            $TemplateTypeArr[$TemplateType['id']] = $TemplateType['name'];
        }

        foreach ($list as &$val) {
            if ($val[$field] && isset($TemplateTypeArr[$val[$field]])) {
                $val[$fieldKey] = $TemplateTypeArr[$val[$field]];
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
    public function setTemplateType(array &$arr, string $field = 'pur_type_id', $fieldKey = 'pur_type_name') {
        if (empty($arr)) {
            return;
        }
        $TemplateTypeId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $TemplateTypeId = $arr[$field];
        }
        $fields = 'id,name';
        if (empty($TemplateTypeId)) {
            return $arr;
        }

        $TemplateType = $this->model
                ->selectRaw($fields)
                ->where('id', $TemplateTypeId)
                ->where('enable', '1')
                ->first();

        $arr[$fieldKey] = !empty($TemplateType) ? $TemplateType->name : '';
    }

}
