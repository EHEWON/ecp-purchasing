<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectThanks extends BaseModel {

    protected $table = 'project_thanks';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'org_id' => 'string',
        'updated_by' => 'string',
        'project_id' => 'string',
        'bid_mode_id' => 'string',
        'template_id' => 'string',
        'created_by' => 'string',
    ];

}
