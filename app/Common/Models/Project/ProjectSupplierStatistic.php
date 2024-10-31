<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectSupplierStatistic extends BaseModel {

    protected $table = 'project_supplier_statistic';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'supplier_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'org_id' => 'string',
    ];

}
