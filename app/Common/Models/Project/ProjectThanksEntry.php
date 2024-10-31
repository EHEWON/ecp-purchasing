<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectThanksEntry extends BaseModel {

    protected $table = 'project_thanks_entry';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'supplier_id' => 'string',
        'updated_by' => 'string',
        'auditor_id' => 'string',
        'created_by' => 'string',
        'thanks_id' => 'string',
    ];

}
