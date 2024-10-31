<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectInvitation extends BaseModel {

    protected $table = 'project_invitation';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'org_id' => 'string',
        'bid_project_id' => 'string',
        'entity_type_id' => 'string',
        'source_bill_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'audited_by' => 'string',
    ];

}
