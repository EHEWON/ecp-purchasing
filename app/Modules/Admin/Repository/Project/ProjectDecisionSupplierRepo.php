<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\{
    ProjectDecisionSupplier,
    ProjectOpenSupplier,
    ProjectSupplier
};
use App\Modules\Admin\Repository\SupplierBaseRepo;
use Illuminate\Http\Request;

class ProjectDecisionSupplierRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectDecisionSupplier();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId);
        $object = $qurey->orderBy('created_at', 'DESC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new SupplierBaseRepo)->setSuppliers($data);
        return $data;
    }

    public function updateData(int $projectId, Request $request) {
        $supplierList = $request->supplier;
        if (empty($supplierList)) {
            return;
        }
        $decisionStatus = $request->decision['decision_status'];
        foreach ($supplierList as $key => $supplier) {
            $dataList[] = [
                'project_id' => $projectId,
                'seq' => $key + 1,
                'supplier_id' => !empty($supplier['supplier_id']) ? $supplier['supplier_id'] : null,
                'adopt_flag' => !empty($supplier['adopt_flag']) ? $supplier['adopt_flag'] : ($decisionStatus == 'C' ? 2 : null),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($decisionStatus == 'C') {
                $amount = null;
                if ($supplier['adopt_flag'] === '1') {
                    $amount = \App\Common\Models\Project\ProjectBidQuote::where('project_id', $projectId)
                            ->where('supplier_id', $supplier['supplier_id'])
                            ->value('inclu_tax_amount');
                }
                ProjectSupplier::where('project_id', $projectId)
                        ->where('supplier_id', $supplier['supplier_id'])
                        ->where('is_tender', '1')
                        ->update([
                            'status' => $supplier['adopt_flag'] === '1' ? 'F' : 'G',
                            'winning_at' => $supplier['adopt_flag'] === '1' ? date('Y-m-d H:i:s') : null,
                            'winning_amount' => $supplier['adopt_flag'] === '1' ? $amount : null,
                ]);
            }
        }
        return ProjectDecisionSupplier::upsert($dataList, ['project_id', 'supplier_id'], ['adopt_flag', 'updated_at']);
    }

    public function init(int $projectId) {

        $otable = (new ProjectOpenSupplier)->getTable();
        $model = new \App\Common\Models\Project\ProjectBidQuote();
        $qTable = $model->getTable();
        $quoteObj = $model::from($qTable . ' as q')
                ->join($otable . ' AS op', function($join) {
                    $join->on('op.project_id', '=', 'q.project_id')
                    ->on('op.supplier_id', '=', 'q.supplier_id');
                })
                ->where('op.is_inval_id', '0')
                ->where('q.project_id', $projectId)
                ->where('q.bid_status', 'C')
                ->selectRaw('q.*')
                ->orderBy('q.tended_at', 'ASC')
                ->get();
        $quoteList = !empty($quoteObj) ? $quoteObj->toArray() : [];
        $adoptFlag = '0';
        $adoptBool = false;
        $dataList = [];
        $decideFlag = min(array_column($quoteList, 'inclu_tax_amount'));
        foreach ($quoteList as $key => $quote) {
            $supplierId = !empty($quote['supplier_id']) ? $quote['supplier_id'] : null;
            if ($adoptBool === false) {
                $adoptFlag = !empty($quote['inclu_tax_amount']) && $quote['inclu_tax_amount'] == $decideFlag ? '1' : '2';
            } else {
                $adoptFlag = '2';
            }
            if ($adoptFlag == '1') {
                $adoptBool = true;
            }
            $dataList[] = [
                'project_id' => $projectId,
                'seq' => $key + 1,
                'supplier_id' => $supplierId,
                'tax_rate' => !empty($quote['tax_rate']) ? $quote['tax_rate'] : null,
                'inclu_tax_amount' => !empty($quote['inclu_tax_amount']) ? $quote['inclu_tax_amount'] : 0,
                'tax_amount' => !empty($quote['tax_amount']) ? $quote['tax_amount'] : null,
                'exc_tax_amount' => !empty($quote['exc_tax_amount']) ? $quote['exc_tax_amount'] : null,
                'adopt_flag' => $adoptFlag,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        return ProjectDecisionSupplier::upsert($dataList, ['project_id', 'supplier_id'], ['adopt_flag', 'updated_at']);
    }

}
