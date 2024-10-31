<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectBidQuote extends BaseModel {

    protected $table = 'project_bid_quote';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
                'created_by' => 'string',
        'updated_by' => 'string',
        'project_id' => 'string',
        'supplier_id' => 'string',
    ];

}
