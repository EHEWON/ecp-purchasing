<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectTemplateType extends BaseModel {

    protected $table = 'project_template_type';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'parent_id' => 'string'
    ];

}
