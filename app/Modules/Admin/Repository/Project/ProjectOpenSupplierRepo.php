<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\{
    ProjectOpenSupplier,
    ProjectPay,
    ProjectSupplier
};
use App\Modules\Admin\Repository\SupplierBaseRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectOpenSupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectOpenSupplier();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['pay_flag_name'] = $this->getPayFlagText($item['pay_flag']);
        }
        (new SupplierBaseRepo)->setSuppliers($list, 'supplier_id', 'supplier_name');
        return $list;
    }

    public function updateData(int $projectId, Request $request) {
        $dataList = $this->getSupplierss($projectId, $request);
        if (!empty($dataList)) {
            ProjectOpenSupplier::upsert($dataList, ['project_id', 'supplier_id'], ['is_inval_id', 'inval_reason', 'updated_by', 'updated_at']);
        }
    }

    public function getSupplierss(int $projectId, Request $request) {
        $dataList = [];
        $admin = Auth::guard('admin')->user();
        $supplierIdObj = ProjectOpenSupplier::where('project_id', $projectId)
                ->where('is_tender', '1')
                ->pluck('supplier_id');
        $supplierIds = !empty($supplierIdObj) ? $supplierIdObj->toArray() : [];
        if (!empty($request->supplier)) {
            foreach ($request->supplier as $supplier) {
                if (empty($supplier['supplier_id'])) {
                    continue;
                }
                $isInvalId = in_array($supplier['supplier_id'], $supplierIds) ? '1' : '0';
                $dataList[] = [
                    'project_id' => $projectId,
                    'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : null,
                    'is_inval_id' => isset($supplier['is_inval_id']) && $supplier['is_inval_id'] == '0' ? '0' : $isInvalId,
                    'inval_reason' => !empty($supplier['inval_reason']) ? $supplier['inval_reason'] : '',
                    'created_by' => $admin->user_id,
                    'updated_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $dataList;
    }

    public function Init(int $projectId) {
        $dataList = [];
        ProjectSupplier::where('project_id', $projectId)
                ->where('shortlist_flag', 'Y')
                ->where('is_tender', '2')
                ->update(['status' => 'H']);
        $supplierObj = ProjectSupplier::where('project_id', $projectId)
                ->where('shortlist_flag', 'Y')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        $this->getPayList($supplierList, $projectId);
        foreach ($supplierList as $supplier) {
            $dataList[] = [
                'project_id' => $projectId,
                'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : null,
                'supplier_contact' => !empty($supplier['supplier_contact']) ? $supplier['supplier_contact'] : '',
                'contact_phone' => !empty($supplier['contact_phone']) ? $supplier['contact_phone'] : '',
                'contact_email' => !empty($supplier['contact_email']) ? $supplier['contact_email'] : '',
                'supplier_comment' => !empty($supplier['supplier_comment']) ? $supplier['supplier_comment'] : '',
                'supplier_source' => !empty($supplier['supplier_source']) ? $supplier['supplier_source'] : '',
                'supplier_name' => !empty($supplier['supplier_name']) ? $supplier['supplier_name'] : '',
                'supplier_deposit' => !empty($supplier['supplier_deposit']) ? $supplier['supplier_deposit'] : null,
                'is_tender' => !empty($supplier['is_tender']) && $supplier['is_tender'] == '1' ? '1' : '0',
                'tended_at' => !empty($supplier['tended_at']) ? $supplier['tended_at'] : null,
                'status' => !empty($supplier['status']) ? $supplier['status'] : '',
                'pay_flag' => !empty($supplier['pay_flag']) ? $supplier['pay_flag'] : 'N',
                'is_inval_id' => !empty($supplier['is_tender']) && $supplier['is_tender'] !== '1' ? '1' : '0',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        ProjectOpenSupplier::insert($dataList);
    }

    public function getPayList(&$supplierList, $projectId) {
        if (empty($supplierList)) {
            return [];
        }
        $supplierIds = [];
        foreach ($supplierList as &$supplier) {
            $supplier['supplier_deposit'] = null;
            $supplierIds[] = $supplier['supplier_id'];
        }
        $payObj = ProjectPay::selectRaw('supplier_id,real_amount')
                ->where('project_id', $projectId)
                ->where('bill_status', 'C')
                ->whereIn('supplier_id', $supplierIds)
                ->where('type', 'EARNEST')
                ->get();
        if (empty($payObj)) {
            return [];
        }
        $payList = $payObj->toArray();
        $payArr = [];
        foreach ($payList as $pay) {
            $payArr[$pay['supplier_id']] = $pay['real_amount'];
        }
        foreach ($supplierList as &$supplier) {
            if (!empty($payArr[$supplier['supplier_id']])) {
                $supplier['supplier_deposit'] = $payArr[$supplier['supplier_id']];
            }
        }
    }

    public function getPayFlagText($payFlag) {
        switch (strtoupper($payFlag)) {
            case 'N':
                return '应缴未缴';
            case 'Y':
                return '已缴纳';
            case 'EARNEST':
                return '应缴未缴';
            case 'DOCUMENT':
                return '应缴未缴';
            default:
                return '应缴未缴';
        }
    }

}
