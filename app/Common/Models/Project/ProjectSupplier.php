<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectSupplier extends BaseModel {

    protected $table = 'project_supplier';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'project_id' => 'string',
        'supplier_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

}