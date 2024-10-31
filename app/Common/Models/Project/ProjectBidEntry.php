<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectBidEntry extends BaseModel {

    protected $table = 'project_bid_entry';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'quote_id' => 'string',
        'pur_project_id' => 'string',
        'tax_rate_id' => 'string',
    ];

}
