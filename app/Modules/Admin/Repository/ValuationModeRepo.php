<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\ValuationMode;
use Illuminate\Http\Request;

class ValuationModeRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ValuationMode();
        parent::__construct($this->model);
    }

    public function getAll() {
        $fields = 'id,name,number,is_pre_setting,org_id';
        $qurey = $this->model->selectRaw($fields);
        $qurey->where('enable', '1');
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
    public function setValuationModes(array &$list, $filed = 'bid_model_id', $fieldKey = 'bid_model_name') {
        if (empty($list)) {
            return;
        }
        $ValuationModeIds = [];
        foreach ($list as &$val) {
            if (isset($val[$filed]) && $val[$filed]) {
                $ValuationModeIds[] = $val[$filed];
            }
            $val[$fieldKey] = '';
        }
        if (empty($ValuationModeIds)) {
            return $list;
        }
        $fields = 'id,name';
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('id', $ValuationModeIds)
                ->where('enable', '1');
        $ValuationModeObjects = $qurey->get();
        if (empty($ValuationModeObjects)) {
            return $list;
        }
        $ValuationModes = $ValuationModeObjects->toArray();
        $ValuationModeArr = [];
        foreach ($ValuationModes as $ValuationMode) {
            $ValuationModeArr[$ValuationMode['id']] = $ValuationMode['name'];
        }

        foreach ($list as &$val) {
            if ($val[$filed] && isset($ValuationModeArr[$val[$filed]])) {
                $val[$fieldKey] = $ValuationModeArr[$val[$filed]];
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
    public function setValuationMode(array &$arr, string $field = 'bid_model_id', $fieldKey = 'bid_model_name') {
        if (empty($arr)) {
            return;
        }
        $ValuationModeId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $ValuationModeId = $arr[$field];
        }
        $fields = 'id,name';
        if (empty($ValuationModeId)) {
            return $arr;
        }

        $ValuationMode = $this->model
                ->selectRaw($fields)
                ->where('id', $ValuationModeId)
                ->where('enable', '1')
                ->first();
        $arr[$fieldKey] = !empty($ValuationMode) ? $ValuationMode->name : '';
    }

    /**
     * 获取合同列表
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
