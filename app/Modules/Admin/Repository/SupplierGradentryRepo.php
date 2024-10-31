<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\SupplierGradentry;
use App\Modules\Admin\Repository\SupplierEvaGradeRepo;

class SupplierGradentryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierGradentry();
        parent::__construct($this->model);
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setGradentrys(array &$list, string $field = 'id') {
        if (empty($list)) {
            return;
        }
        $gradeIds = [];
        foreach ($list as &$val) {
            $val['gradentry'] = [];
            if (isset($val[$field]) && $val[$field]) {
                $gradeIds[] = $val[$field];
            }
        }

        if (empty($gradeIds)) {
            return $list;
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->whereIn('grade_id', $gradeIds);
        $groupObjects = $qurey
                ->orderBy('seq', 'ASC')
                ->get();
        if (empty($groupObjects)) {
            return $list;
        }

        $gradentrys = $groupObjects->toArray();
        (new SupplierEvaGradeRepo)->setEvaGrades($gradentrys);
        $groupArr = [];
        foreach ($gradentrys as $gradentry) {
            $groupArr[$gradentry['grade_id']][] = $gradentry;
        }

        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($groupArr[$val[$field]])) {
                $val['gradentry'] = $groupArr[$val[$field]];
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
    public function setGradentry(array &$arr, string $field = 'id') {
        if (empty($arr)) {
            return;
        }
        $gradeId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $gradeId = $arr[$field];
        }
        $arr['gradentry'] = [];
        if (empty($gradeId)) {
            return $arr;
        }
        $gradentrys = $this->model
                ->selectRaw('*')
                ->where('grade_id', $gradeId)
                ->orderBy('seq', 'ASC')
                ->get()
                ->toArray();
        (new SupplierEvaGradeRepo)->setEvaGrades($gradentrys);
        $arr['gradentry'] = $gradentrys;
    }

}
