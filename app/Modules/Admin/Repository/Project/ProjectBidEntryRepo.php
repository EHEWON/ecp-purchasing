<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\ProjectBidEntry;
use App\Modules\Admin\Repository\{
    PurProjectRepo,
    TaxRateRepo
};
use Illuminate\Http\Request;

class ProjectBidEntryRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectBidEntry();
        parent::__construct($this->model);
    }

    public function getList(int $quoteId) {
        if (empty($quoteId)) {
            return [];
        }

        $qurey = $this->model->selectRaw('*');
        $qurey->where('quote_id', $quoteId);
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new TaxRateRepo)->setTaxRates($list, 'tax_rate_id', 'tax_rate_name');
        (new PurProjectRepo)->setPurProjects($list, 'pur_project_id', 'pur_project_name');
        return $list;
    }

    public function updateData(int $quote_id, Request $request) {
        ProjectBidEntry::where('quote_id', $quote_id)->delete();
        $entryList = $request->entrys;
        $dataList = [];
        $projectId = $request->base['project_id'];
        foreach ($entryList as $key => $entry) {
            $dataList[] = [
                'quote_id' => $quote_id,
                'project_id' => $projectId,
                'entry_id' => !empty($entry['entry_id']) ? $entry['entry_id'] : '0',
                'purentry_content' => !empty($entry['purentry_content']) ? $entry['purentry_content'] : '',
                'pur_project_id' => !empty($entry['pur_project_id']) ? $entry['pur_project_id'] : '',
                'inclu_tax_price' => !empty($entry['inclu_tax_price']) ? $entry['inclu_tax_price'] : '0',
                'inclu_tax_amount' => !empty($entry['inclu_tax_amount']) ? $entry['inclu_tax_amount'] : '0',
                'tax_rate_id' => !empty($entry['tax_rate_id']) ? $entry['tax_rate_id'] : null,
                'tax_amount' => !empty($entry['tax_amount']) ? $entry['tax_amount'] : '0',
                'tax_rate' => !empty($entry['tax_rate']) ? $entry['tax_rate'] : null,
                'except_tax_amount' => !empty($entry['except_tax_amount']) ? $entry['except_tax_amount'] : '0',
            ];
        }
        if (empty($dataList)) {
            return;
        }
        return ProjectBidEntry::insert($dataList);
    }

}
