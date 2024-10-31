<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Setting;
use Illuminate\Http\Request;

class SettingRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Setting();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'id,`group`,alias,value,serialized,created_at,updated_at') {
        $query = $this->model
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        return $object->toArray();
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function updateOrAdd(Request $request) {
        $dataList = [];
        $list = $request->all();
        $aliasies = [];
        foreach ($list as $item) {
            $aliasies[] = $item['alias'];
            $dataList[] = [
                'group' => !empty($item['group']) ? strtoupper($item['group']) : 'SYSTEM',
                'alias' => trim($item['alias']),
                'value' => trim($item['value']),
                'serialized' => !empty($item['serialized']) ? intval($item['serialized']) : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        !empty($aliasies) ? Setting::whereNotIn('alias', $aliasies)->delete() : Setting::delete();
        return Setting::upsert($dataList, ['alias'], ['group', 'alias', 'value', 'serialized', 'updated_at']);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Setting::whereIn('id', $ids)->delete();
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {

        if (!empty($request->group)) {
            $query->whereIn('group', $request->group);
        }
        if (!empty($request->alias)) {
            $query->whereIn('alias', $request->alias);
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
