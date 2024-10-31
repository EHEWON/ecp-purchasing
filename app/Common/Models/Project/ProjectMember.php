<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectMember extends BaseModel {

    protected $table = 'project_member';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'user_id' => 'string',
        'project_id'=>'string',
        'position_id' => 'string',
    ];

}
