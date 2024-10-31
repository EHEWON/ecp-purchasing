<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Project\ProjectThanks,
    Project\ProjectThanksEntry,
    UserSupplier,
    Supplier AS BaseSupplier
};
use Illuminate\Support\Facades\Auth;

class ProjectThanksRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectThanks();
        parent::__construct($this->model);
        $this->admin = Auth::guard('admin')->user();
        if (empty($this->admin->user_type) || $this->admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $this->userId = $this->admin->user_id;
        $this->supplierId = UserSupplier::where('user_id', $this->userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($this->supplierId)) {
            check(false, '您没有关联供应商');
        }
        $supplier = BaseSupplier::where('id', $this->supplierId)
                ->selectRaw('id,status,deleted_flag,enable,source,name')
                ->first();
        check(!empty($supplier), '供应商不存在');
        check($supplier->deleted_flag == 'N', '供应商已被删除');
        check($supplier->status === 'APPROVED', '供应商没有准入');
        check($supplier->enable == '1', '供应商已被禁用');
        $this->source = $supplier->source;
        $this->supplierName = $supplier->name;
    }

    public function info(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $obj = ProjectThanks::where('project_id', $projectId)
                ->selectRaw('project_id,name,deadline_date,publish_date as bill_date,org_id,id')
                ->first();
        if (empty($obj)) {
            return[];
        }
        $data = $obj->toArray();
        $data['content'] = '';
        $invitationId = $obj->id;
        $entry = ProjectThanksEntry::where('invitation_id', $invitationId)
                ->where('supplier_id', $this->supplierId)
                ->first();
        if (empty($entry)) {
            return[];
        }
        $data['content'] = $entry->content;
        return $data;
    }

}
