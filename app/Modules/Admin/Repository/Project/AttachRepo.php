<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\Attach;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Attach();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId)
                ->whereIn('group', ['PROJECT']);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
        }
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        return $list;
    }

    public function updateData(int $projectId, Request $request) {
        Attach::where('project_id', $projectId)->whereIn('group', ['PROJECT'])->delete();
        $attachList = $this->getAttachs($projectId, $request);
        if (!empty($attachList)) {
            Attach::insert($attachList);
        }
    }

    public function getAttachs(int $projectId, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'group' => 'PROJECT',
                    'project_id' => $projectId,
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $attachList;
    }

    public function getShortList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId)
                ->whereIn('group', ['INVITATION']);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
        }
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        return $list;
    }

}
