<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\NoticeSub;

class NoticeSubRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new NoticeSub();
        parent::__construct($this->model);
    }

}
