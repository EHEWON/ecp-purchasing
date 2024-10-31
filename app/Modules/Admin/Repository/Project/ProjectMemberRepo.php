<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Project\ProjectMember,
    RoleUsers,
    Roles,
    User,
    UserPurchaser
};
use App\Modules\Admin\Repository\{
    PurchaserRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectMemberRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectMember();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }

        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $respBusinessList = explode(',', $item['resp_business']);
            $item['resp_business'] = $respBusinessList;
            $item['resp_business_name'] = $this->getRespBusinessText($respBusinessList);
        }
        (new UserRepo)->setUsers($list, 'user_id', 'user_name');
        return $list;
    }

    public function updateData(int $projectId, Request $request) {
        ProjectMember::where('project_id', $projectId)->delete();
        if (empty($request->member)) {
            return [];
        }
        $memberList = $request->member;
        $dataList = [];
        $purchaserId = $this->getPPurchaserId();
        foreach ($memberList as $key => $member) {
            if (empty($member['user_id'])) {
                continue;
            }
            $dataList[] = [
                'project_id' => $projectId,
                'user_id' => !empty($member['user_id']) ? $member['user_id'] : '0',
                'position' => !empty($member['position']) ? $member['position'] : '0',
                'seq' => $key + 1,
                'is_director' => !empty($member['is_director']) ? intval($member['is_director']) : '0',
                'comment' => !empty($member['comment']) ? $member['comment'] : '',
                'resp_business' => !empty($member['resp_business']) ? implode(',', $member['resp_business']) : '',
                'purchaser_id' => !empty($member['purchaser_id']) ? $member['purchaser_id'] : $purchaserId,
                'purchaser_name' => !empty($member['purchaser_name']) ? $member['purchaser_name'] : '',
                'user_name' => !empty($member['user_name']) ? $member['user_name'] : '',
                'user_phone' => !empty($member['user_phone']) ? $member['user_phone'] : '',
                'create_time' => date('Y-m-d H:i:s'),
                'modify_time' => date('Y-m-d H:i:s'),
            ];
        }
        if (empty($dataList)) {
            return;
        }
        return ProjectMember::insert($dataList);
    }

    public function getRespBusinessText($respBusinessList) {

        $respBusinessArr = [];
        foreach ($respBusinessList as $resp) {
            switch (strtoupper($resp)) {
                case 'A':
                    $respBusinessArr[] = '招标立项';
                    break;
                case 'B':
                    $respBusinessArr[] = '入围邀请';
                    break;
                case 'C':
                    $respBusinessArr[] = '标书编制';
                    break;
                case 'D':
                    $respBusinessArr[] = '标底编制';
                    break;
                case 'E':
                    $respBusinessArr[] = '招标交底';
                    break;
                case 'F':
                    $respBusinessArr[] = '发标';
                    break;
                case 'G':
                    $respBusinessArr[] = '答疑';
                    break;
                case 'H':
                    $respBusinessArr[] = '开标';
                    break;
                case 'I':
                    $respBusinessArr[] = '评标';
                    break;
                case 'J':
                    $respBusinessArr[] = '商务谈判';
                    break;
                case 'K':
                    $respBusinessArr[] = '定标';
                    break;
            }
        }
        return $respBusinessArr;
    }

    public function members(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userTable = (new User)->getTable();
        $roleUserTable = (new RoleUsers)->getTable();
        $roleTable = (new Roles())->getTable();
        $upTable = (new UserPurchaser())->getTable();
        $pTable = (new \App\Common\Models\Purchaser())->getTable();
        $curId = $this->getPPurchaserId();
        $upQuery = UserPurchaser::from($upTable . ' as ups')
                ->join($pTable . ' as p', function($join) {
                    $join->on('p.id', '=', 'ups.bot_purchaser_id');
                })
                ->selectRaw('ups.user_id,max(ups.id) as max_up_id')
                ->whereRaw('p.id= ' . $curId)
                ->where('p.deleted_flag', 'N')
                ->where('ups.deleted_flag', 'N')
                ->groupBy('ups.user_id');
        $ruQuery = UserPurchaser::from($roleUserTable . ' as ru')
                ->selectRaw('ru.user_id,ru.role_id')
                ->whereIn('ru.role_group', ['PURCHASER', 'PLATFORM', 'SYSTEM', 'COMMON'])
                ->where('ru.deleted_flag', 'N')
                ->groupBy('ru.user_id')
                ->groupBy('ru.role_id');
        $query = User::from($userTable . ' AS u')
                ->joinSub($ruQuery, 'rmax', function($join) {
                    $join->on('rmax.user_id', '=', 'u.user_id');
                })
                ->leftJoin($roleTable . ' as r', function($join) {
                    $join->on('r.id', '=', 'rmax.role_id')
                    ->where('r.role_no', 'bidding_team');
                })
                ->joinSub($upQuery, 'max', function ($join) {
                    $join->on('max.user_id', '=', 'u.user_id');
                })
                ->join($upTable . ' AS up', function ($join) {
                    $join->on('up.id', '=', 'max.max_up_id');
                })
                ->selectRaw('u.user_id,u.user_type,u.phone,u.realname,u.email,u.image,'
                        . 'up.position,up.bot_purchaser_id as purchaser_id,u.gender')
                ->where('r.deleted_flag', 'N')
                ->whereIn('r.role_group', ['PURCHASER', 'PLATFORM', 'SYSTEM', 'COMMON'])
//                ->whereIn('ru.role_group', ['PURCHASER', 'PLATFORM', 'SYSTEM', 'COMMON'])
                ->whereIn('u.user_type', ['PURCHASER', 'PLATFORM'])
                ->where('r.status', 'NORMAL')
                ->where('u.deleted_flag', 'N')
                ->where('u.enable', '1')
                ->where('u.status', '1')
                ->where(function ($q)use($admin) {
            $q->whereNotNull('r.id')
            ->orWhere('u.user_id', $admin->user_id);
        });
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('u.realname', 'like', '%' . $keyword . '%')
                        ->orWhere('u.phone', 'like', '%' . $keyword . '%')
                        ->orWhere('u.email', 'like', '%' . $keyword . '%');
            });
        }
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $query->orderBy('u.created_at', 'DESC');
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new PurchaserRepo)->setPurchasers($data);
        foreach ($data as &$item) {
            $item['purchaser_id'] = (string) $item['purchaser_id'];
        }
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function setRespBusinesies(&$list, $field = 'project_id') {
        $admin = Auth::guard('admin')->user();
        $projectIds = [];
        foreach ($list as &$item) {
            $item['resp_business'] = [];
            if ($item['bill_status'] === 'C') {
                $projectIds[] = $item[$field];
            }
        }
        if (empty($admin) || empty($projectIds)) {
            return;
        }
        $userId = $admin->user_id;
        $object = ProjectMember::whereIn('project_id', $projectIds)
                ->where('user_id', $userId)
                ->get();
        if (empty($object)) {
            return;
        }
        $memberList = $object->toArray();
        $memberArr = [];
        foreach ($memberList as $member) {
            $memberArr[$member['project_id']] = explode(',', $member['resp_business']);
        }
        foreach ($list as &$item) {
            if (!empty($memberArr[$item[$field]])) {
                $item['resp_business'] = $memberArr[$item[$field]];
            }
        }
    }

    public function setRespBusiness(&$data, $field = 'project_id') {
        $admin = Auth::guard('admin')->user();
        $data['resp_business'] = [];
        if ($data['bill_status'] !== 'C') {
            return;
        }
        $projectId = $data[$field];
        if (empty($admin)) {
            return;
        }
        $userId = $admin->user_id;
        $object = ProjectMember::where('project_id', $projectId)
                ->where('user_id', $userId)
                ->first();
        if (empty($object)) {
            return;
        }
        $data['resp_business'] = explode(',', $object->resp_business);
    }

}
