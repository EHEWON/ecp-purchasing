<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\ProjectBidQuote;
use App\Modules\Admin\Repository\{
    Project\ProjectBidAttachRepo,
    Project\ProjectBidEntryRepo,
    Project\ProjectRepo,
    SupplierBaseRepo
};

class ProjectBidQuoteRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectBidQuote();
        parent::__construct($this->model);
    }

    public function info(int $quoteId) {
        if (empty($quoteId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('id', $quoteId)
                ->where('bid_status', 'C');
        $object = $qurey->orderBy('id', 'ASC')->first();
        if (empty($object)) {
            return [];
        }
        $base = $object->toArray();
        (new SupplierBaseRepo)->setSupplier($base, 'supplier_id', 'supplier_name');
        $base['except_tax_amount'] = number_format($base['except_tax_amount'], 2, '.', '');
        $base['inclu_tax_amount'] = number_format($base['inclu_tax_amount'], 2, '.', '');
        $base['tax_amount'] = number_format($base['tax_amount'], 2, '.', '');
        $base['tax_rate'] = number_format($base['tax_rate'], 2, '.', '');
        $data['base'] = $base;
        $data['attachs'] = (new ProjectBidAttachRepo)->getList($base['id']);
        $data['entrys'] = (new ProjectBidEntryRepo)->getList($base['id']);
        $data['process'] = (new ProjectRepo)->getProcess($base['project_id']);
        return $data;
    }

}
