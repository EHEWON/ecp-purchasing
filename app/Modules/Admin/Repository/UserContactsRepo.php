<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\UserContacts;

class UserContactsRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new UserContacts();
        parent::__construct($this->model);
    }

}
