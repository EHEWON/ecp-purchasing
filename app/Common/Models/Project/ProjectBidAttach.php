<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectBidAttach extends BaseModel {

    protected $table = 'project_bid_attach';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

}
