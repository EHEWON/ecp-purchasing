<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\{
    ProjectBidEntry,
    ProjectDecisionEntry,
    ProjectOpenSupplier
};
use App\Modules\Admin\Repository\SupplierBaseRepo;
use Illuminate\Http\Request;

class ProjectDecisionEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectDecisionEntry();
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
        $data = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($data);
        return $data;
    }

    public function updateData(int $projectId, Request $request) {
        $entryList = $request->entry;
        $dataList = [];
        foreach ($entryList as $key => $entry) {
            $dataList[] = [
                'seq' => $key + 1,
                'project_id' => $projectId,
                'supplier_id' => !empty($entry['supplier_id']) ? $entry['supplier_id'] : null,
                'entry_id' => !empty($entry['entry_id']) ? $entry['entry_id'] : null,
                'adopt_flag' => !empty($entry['adopt_flag']) ? $entry['adopt_flag'] : null,
                'qty' => !empty($entry['qty']) ? $entry['qty'] : null,
                'inclu_tax_price' => !empty($entry['inclu_tax_price']) ? $entry['inclu_tax_price'] : null,
                'inclu_tax_amount' => !empty($entry['inclu_tax_amount']) ? $entry['inclu_tax_amount'] : '0',
                'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : '0',
                'tax_amount' => !empty($entry['tax_amount']) ? $entry['tax_amount'] : '0',
                'except_tax_amount' => !empty($entry['except_tax_amount']) ? $entry['except_tax_amount'] : '0',
                'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        if (empty($dataList)) {
            return;
        }
        return ProjectDecisionEntry::upsert($dataList, ['project_id', 'supplier_id', 'entry_id'], ['adopt_flag', 'updated_at']);
    }

    public function init(int $projectId) {
        $supplierObj = ProjectOpenSupplier::where('project_id', $projectId)
                ->where('is_inval_id', '0')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        $supplierIds = array_column($supplierList, 'supplier_id');
        $entryObj = ProjectBidEntry::where('project_id', $projectId)
                ->whereIn('supplier_id', $supplierIds)
                ->get();
        if (empty($entryObj)) {
            return;
        }
        $entryList = $entryObj->toArray();
        $dataList = [];
        foreach ($entryList as $key => $entry) {
            $dataList[] = [
                'project_id' => $projectId,
                'seq' => $key + 1,
                'supplier_id' => !empty($entry['supplier_id']) ? $entry['supplier_id'] : null,
                'entry_id' => !empty($entry['entry_id']) ? $entry['entry_id'] : null,
                'adopt_flag' => null,
                'qty' => !empty($entry['qty']) ? $entry['qty'] : null,
                'inclu_tax_price' => !empty($entry['inclu_tax_price']) ? $entry['inclu_tax_price'] : null,
                'inclu_tax_amount' => !empty($entry['inclu_tax_amount']) ? $entry['inclu_tax_amount'] : '0',
                'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : '0',
                'tax_amount' => !empty($entry['tax_amount']) ? $entry['tax_amount'] : '0',
                'except_tax_amount' => !empty($entry['except_tax_amount']) ? $entry['except_tax_amount'] : '0',
                'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null,
                'adopt_flag' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        return ProjectDecisionEntry::upsert($dataList, ['project_id', 'supplier_id'], ['adopt_flag', 'updated_at']);
    }

}
