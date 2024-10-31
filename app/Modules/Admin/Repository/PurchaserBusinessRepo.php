<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\PurchaserBusiness;

class PurchaserBusinessRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new PurchaserBusiness();
        parent::__construct($this->model);
    }

}
