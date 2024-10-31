<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Project\ProjectEntry,
    Project\ProjectSub,
    TaxRate
};
use App\Modules\Admin\Repository\{
    PurProjectRepo,
    SupplierBaseRepo,
    TaxRateRepo
};
use Illuminate\Http\Request;

class ProjectEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectEntry();
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
        (new TaxRateRepo)->setTaxRates($list, 'tax_rate_id', 'tax_rate_name');
        (new PurProjectRepo)->setPurProjects($list, 'pur_project_id', 'pur_project_name');
        (new SupplierBaseRepo)->setSuppliers($list);
        foreach ($list as &$item) {
            $item['control_amount'] = is_null($item['control_amount']) ? null : number_format($item['control_amount'], 2, '.', '');
            $item['tax_rate'] = is_null($item['tax_rate']) ? null : number_format($item['tax_rate'], 0, '.', '');
            $item['control_vat'] = is_null($item['control_vat']) ? null : number_format($item['control_vat'], 2, '.', '');
            $item['ctrl_amt_except_vat'] = is_null($item['ctrl_amt_except_vat']) ? null : number_format($item['ctrl_amt_except_vat'], 2, '.', '');
        }
        return $list;
    }

    public function updateData(int $projectId, Request $request) {
        ProjectEntry::where('project_id', $projectId)->delete();
        $entryList = $request->entry;
        $taxRateArr = $this->getTaxRateArr();
        $dataList = [];
        $totalControl = 0;
        $totalCtrlExcVat = 0;
        foreach ($entryList as $key => $entry) {
            if (!empty($entry['tax_rate']) && !empty($taxRateArr[intval($entry['tax_rate'])])) {
                $entry['tax_rate_id'] = $taxRateArr[intval($entry['tax_rate'])]; //申报要素 
            } else {
                $entry['tax_rate_id'] = 0;
            }
            $totalControl += !empty($entry['control_amount']) ? $entry['control_amount'] : 0;
            $controlAmount = !empty($entry['control_amount']) ? $entry['control_amount'] : 0;
            $controlVat = !empty($entry['control_amount']) ? $entry['control_amount'] * $entry['tax_rate'] / 100 : 0;
            $ctrlAmtExceptVat = $controlAmount - $controlVat;
            $totalCtrlExcVat += $ctrlAmtExceptVat;
            $dataList[] = [
                'seq' => $key + 1,
                'project_id' => $projectId,
                'pur_project_id' => !empty($entry['pur_project_id']) ? $entry['pur_project_id'] : null,
                'work_load' => !empty($entry['work_load']) ? $entry['work_load'] : null,
                'purentry_content' => !empty($entry['purentry_content']) ? $entry['purentry_content'] : '',
                'comment' => !empty($entry['comment']) ? $entry['comment'] : '',
                'program_contract_id' => !empty($entry['program_contract_id']) ? $entry['program_contract_id'] : null,
                'control_amount' => !empty($controlAmount) ? $controlAmount : null,
                'control_vat' => !empty($controlVat) ? $controlVat : null,
                'ctrl_amt_except_vat' => !empty($ctrlAmtExceptVat) ? $ctrlAmtExceptVat : null,
                'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null,
                'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        ProjectSub::where('project_id', $projectId)
                ->update([
                    'total_control' => $totalControl,
                    'total_ctrl_exc_vat' => $totalCtrlExcVat,
        ]);
        if (empty($dataList)) {
            return;
        }

        return ProjectEntry::insert($dataList);
    }

    public function getTaxRateArr() {
        $taxrateList = TaxRate::get()->toArray();
        $taxrateArr = [];
        foreach ($taxrateList as $taxRate) {
            $taxrateArr[intval($taxRate['tax_rate'])] = $taxRate['id'];
        }
        return $taxrateArr;
    }

}
