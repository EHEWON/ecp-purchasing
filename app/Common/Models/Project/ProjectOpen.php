<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectOpen extends BaseModel {

    protected $table = 'project_open';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'project_id' => 'string',
        'org_id' => 'string',
    ];

}
