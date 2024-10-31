<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class Attach extends BaseModel {

    protected $table = 'project_attach';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'project_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

}
