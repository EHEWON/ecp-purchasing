<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\ProjectInvitation;
use App\Modules\Admin\Repository\UserRepo;

class ProjectInvitationRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectInvitation();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }

        $qurey = $this->model->selectRaw('*');
        $qurey->where('bid_project_id', $projectId);
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UserRepo)->setUsers($list, 'user_id', 'user_name');
        return $list;
    }

    public function updateData(int $projectId, Request $request) {
        ProjectEntry::where('project_id', $projectId)->delete();
        $memberList = $request->member;
        $dataList = [];
        foreach ($memberList as $key => $member) {
            $dataList[] = [
                'project_id' => $projectId,
                'user_id' => !empty($member['user_id']) ? $member['user_id'] : '0',
                'position_id' => !empty($member['position_id']) ? $member['position_id'] : '0',
                'seq' => $key + 1,
                'is_director' => !empty($member['is_director']) ? $member['is_director'] : '0',
                'comment' => !empty($member['comment']) ? $member['comment'] : '',
                'resp_business' => !empty($member['resp_business']) ? $member['resp_business'] : '',
                'created_by' => 0,
                'updated_by' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        if (empty($dataList)) {
            return;
        }
        return ProjectInvitation::insert($dataList);
    }

}
