<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\OrgPattern;

class OrgPatternRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new OrgPattern();
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
    public function setPatterns(array &$list, string $field = 'org_pattern_id') {
        if (empty($list)) {
            return;
        }
        $orgPatternIds = [];
        foreach ($list as &$val) {
            $val['org_pattern'] = '';
            if (isset($val[$field]) && $val[$field]) {
                $orgPatternIds[] = $val[$field];
            }
        }

        if (empty($orgPatternIds)) {
            return $list;
        }
        $qurey = $this->model->select('id', 'name');
        $qurey->whereIn('id', $orgPatternIds);

        $orgPatternNameObjects = $qurey->get();
        if (empty($orgPatternNameObjects)) {
            return $list;
        }
        $orgPatternNames = $orgPatternNameObjects->toArray();
        $orgPatternArr = [];
        foreach ($orgPatternNames as $orgPattern) {
            $orgPatternArr[$orgPattern['id']] = $orgPattern['name'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($orgPatternArr[$val[$field]])) {
                $val['org_pattern'] = $orgPatternArr[$val[$field]];
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
    public function setPattern(array &$arr, string $field = 'org_pattern_id') {
        if (empty($arr)) {
            return;
        }
        $orgPatternId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $orgPatternId = $arr[$field];
        }

        $arr['org_pattern'] = '';
        if (empty($orgPatternId)) {
            return $arr;
        }
        $arr['org_pattern'] = $this->model
                ->where('id', $orgPatternId)
                ->where('deleted_flag', 'N')
                ->value('name');
    }

}
