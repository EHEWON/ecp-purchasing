<?php

/**
 * Created by PhpStorm.
 * User: wangjh
 * Date: 2018/1/24
 * Time: 08:44
 */

namespace App\Modules\Frontend\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\{
    ProjectEntry,
    ProjectDecisionSupplier
};
use App\Modules\Admin\Repository\{
    UnitRepo,
    PurProjectRepo,
    SupplierBaseRepo
};

class ProjectEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectEntry();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $entryTable = $this->model->getTable();
        $supTable = (new ProjectDecisionSupplier)->getTable();
        $qurey = $this->model
                ->from($entryTable . ' as e')
                ->leftJoin($supTable . ' AS s', function($join) {
                    $join->on('s.project_id', '=', 'e.project_id')
                    ->where('s.adopt_flag', '1');
                })
                ->selectRaw('e.*,s.supplier_id');
        $qurey->where('e.project_id', $projectId);
        $object = $qurey->orderBy('e.id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }

        $list = $object->toArray();
        (new UnitRepo)->setUnits($list, 'unit_id', 'unit_name');
        (new PurProjectRepo)->setPurProjects($list, 'pur_project_id', 'pur_project_name');
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        foreach ($list as &$item) {
            $item['work_load'] = $item['work_load'];
            $item['purentry_content'] = $item['purentry_content'];
        }
        return $list;
    }

}
