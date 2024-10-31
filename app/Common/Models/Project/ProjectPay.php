<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectPay extends BaseModel {

    protected $table = 'project_pay';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
