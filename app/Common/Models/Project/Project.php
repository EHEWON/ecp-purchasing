<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class Project extends BaseModel {

    protected $table = 'project';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'auditor_id' => 'string',
        'org_id' => 'string',
        'pur_type_id' => 'string',
        'bid_valuation_id' => 'string', //计价模式
        'evaluate_decide_way_id' => 'string',
        'source_project_id' => 'string',
        'entity_type_id' => 'string',
//        'pay_cond_id' => 'string',
        'entrustment_supplier' => 'string',
        'invalided_by' => 'string',
        'bid_mode_id' => 'string',
        'contact_id' => 'string',
    ];

}
