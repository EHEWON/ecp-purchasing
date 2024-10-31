<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\Attach;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDecisionFileRepo extends Repository {

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
                ->whereIn('group', ['DECISION', 'EVA_REPORT', 'WIN_REPORT']);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        $ret = ['eva_report' => [], 'win_report' => [], 'attachs' => []];
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
            if ($item['group'] === 'DECISION') {
                $ret['attachs'][] = $item;
            } else {
                $ret[strtolower($item['group'])][] = $item;
            }
        }
        return $ret;
    }

    public function updateData(int $projectId, Request $request) {
        Attach::where('project_id', $projectId)
                ->whereIn('group', ['DECISION', 'EVA_REPORT', 'WIN_REPORT'])
                ->delete();
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
                    'project_id' => $projectId,
                    'group' => 'DECISION',
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        if (!empty($request->eva_report)) {
            foreach ($request->eva_report as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'project_id' => $projectId,
                    'group' => 'EVA_REPORT',
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        if (!empty($request->win_report)) {
            foreach ($request->win_report as $attach) {

                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'project_id' => $projectId,
                    'group' => 'WIN_REPORT',
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

}
