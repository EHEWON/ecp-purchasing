<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectSub extends BaseModel {

    protected $table = 'project_sub';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'evaluate_decide_way_id' => 'string',
        'project_id' => 'string',
    ];

}
