<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Project\Project,
    Project\ProjectEntry,
    PurProject
};
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB
};

class PurProjectRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new PurProject();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getAll($filed = '*') {
        $query = $this->model
                ->selectRaw($filed);
        $object = $query->orderBy('created_at', 'DESC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        return $data;
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setPurProjects(array &$list, $field = 'pur_project_id', $fieldKey = 'pur_project_name') {
        if (empty($list)) {
            return;
        }
        $PurProjectIds = [];
        foreach ($list as &$val) {
            if (isset($val[$field]) && $val[$field]) {
                $PurProjectIds[] = $val[$field];
            }
            $val[$fieldKey] = '';
        }
        if (empty($PurProjectIds)) {
            return $list;
        }
        $fields = 'id,name';
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('id', $PurProjectIds);
        $PurProjectObjects = $qurey->get();
        if (empty($PurProjectObjects)) {
            return $list;
        }
        $PurProjects = $PurProjectObjects->toArray();
        $PurProjectArr = [];
        foreach ($PurProjects as $PurProject) {
            $PurProjectArr[$PurProject['id']] = $PurProject['name'];
        }

        foreach ($list as &$val) {
            if ($val[$field] && isset($PurProjectArr[$val[$field]])) {
                $val[$fieldKey] = $PurProjectArr[$val[$field]];
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
    public function setPurProject(array &$arr, $field = 'pur_project_id', $fieldKey = 'pur_project_name') {
        if (empty($arr)) {
            return;
        }
        $PurProjectId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $PurProjectId = $arr[$field];
        }
        $fields = 'id,name';

        if (empty($PurProjectId)) {
            return $arr;
        }

        $PurProject = $this->model
                ->selectRaw($fields)
                ->where('id', $PurProjectId)
                ->first();
        $arr[$fieldKey] = $PurProject->name;
    }

    /**
     * 
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
        (new PurchaserRepo)->setPurchasers($data, 'org_id', 'org_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
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

}
