<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    UserPurchaser,
    Purchaser,
    User
};

class UserPurchaserRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new UserPurchaser();
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
    public function setOrgs(array &$list, string $field = 'user_id', $purchaserId = null) {
        if (empty($list)) {
            return;
        }
        $userIds = [];
        foreach ($list as &$val) {
            $val['user_purchaser'] = [];
            if (!in_array($val['user_type'], ['PURCHASER', 'PLATFORM', 'ORG'])) {
                continue;
            }
            if (isset($val[$field]) && $val[$field]) {
                $userIds[] = $val[$field];
            }
        }
        if (empty($userIds)) {
            return $list;
        }
        $purchaserTable = (new Purchaser)->getTable();
        $table = $this->model->getTable();
        $qurey = $this->model
                ->from($table . ' as up')
                ->join($purchaserTable . ' as p', function($join) {
                    $join->on('p.id', 'up.purchaser_id');
                })
                ->where('up.deleted_flag', 'N')
                ->where('p.deleted_flag', 'N')
                ->where('p.enable', 1)
                ->selectRaw('up.*,p.name,p.long_name,p.parent_ids');
        $qurey->whereIn('user_id', $userIds);
        if (!empty($purchaserId)) {
            $qurey->where(function($q)use($purchaserId) {
                $q->where('p.id', $purchaserId)
                        ->orWhereRaw('FIND_IN_SET(' . $purchaserId . ',p.parent_ids)');
            });
        }
        $orgObjects = $qurey
                ->orderBy('up.is_default', 'DESC')
                ->orderBy('up.sort', 'ASC')
                ->get();
        if (empty($orgObjects)) {
            return $list;
        }
        if (!empty($purchaserId)) {
            $purchaser = Purchaser::
                    selectRaw('id as purchaser_id,\'\' as  position,0 as is_manager,'
                            . '0 as is_default,0 as sort,name,long_name,parent_ids')
                    ->where('id', $purchaserId)
                    ->where('deleted_flag', 'N')
                    ->first();
        }

        $orgs = $orgObjects->toArray();
        $orgArr = [];
        foreach ($orgs as $org) {
            $orgArr[$org['user_id']][] = $org;
        }
        foreach ($list as &$val) {
            if (!in_array($val['user_type'], ['PURCHASER', 'PLATFORM', 'ORG'])) {
                continue;
            }
            if (!empty($purchaser) && !empty($purchaserId)) {
                $val['user_purchaser'][] = $purchaser->toArray();
                $val['user_purchaser'][0]['user_id'] = $val['user_id'];
            } elseif (isset($val[$field]) && isset($orgArr[$val[$field]])) {
                $val['user_purchaser'] = $orgArr[$val[$field]];
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
    public function setOrg(array &$val, string $field = 'user_id') {
        if (empty($val) || !in_array($val['user_type'], ['PURCHASER', 'PLATFORM', 'ORG'])) {
            return;
        }
        $userId = [];
        $val['user_purchaser'] = [];
        if (isset($val[$field]) && $val[$field]) {
            $userId = $val[$field];
        }
        if (empty($userId)) {
            return $val;
        }
        $purchaserTable = (new Purchaser())->getTable();
        $qurey = $this->model->selectRaw('*');
        $qurey->where('user_id', $userId);
        $qurey->whereRaw('EXISTS(SELECT id FROM ' . $purchaserTable
                . ' As p WHERE p.id=user_purchaser.purchaser_id'
                . ' AND p.deleted_flag=\'N\' AND p.enable=1)');
        $qurey->where('deleted_flag', 'N');
        $orgObjects = $qurey
                ->get();
        if (empty($orgObjects)) {
            return $val;
        }

        $orgs = $orgObjects->toArray();
        (new OrgRepo)->setOrgs($orgs, 'purchaser_id', 'purchaser_name', true);
        $val['user_purchaser'] = $orgs;
        return $val;
    }

    /**
     * Description of 获取创建人姓名
     * @param int $userId
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function getUserOrgIds(int $userId, $curId = null) {
        $userObj = User::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->first();
        if (empty($userObj)) {
            return [];
        }
        $user = $userObj->toArray();
        if (!in_array($user['user_type'], ['PLATFORM', 'PURCHASER', 'ORG'])) {
            return [];
        }
        $orgRepo = new OrgRepo();
        $orgIds = $orgRepo->getCurPurChaserOrgIds($userId, $curId);
        if (empty($orgIds)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->whereIn('purchaser_id', $orgIds);
        $purchaserIds = $qurey
                ->pluck('purchaser_id');
        if (empty($purchaserIds)) {
            return [];
        }
        return $purchaserIds;
    }

}
