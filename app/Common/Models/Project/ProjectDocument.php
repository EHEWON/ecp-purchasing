<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectDocument extends BaseModel {

    protected $table = 'project_document';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
   protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'project_id' => 'string'
    ];
}
