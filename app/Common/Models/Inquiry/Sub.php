<?php

namespace App\Common\Models\Inquiry;

use App\Common\Models\BaseModel;

class Sub extends BaseModel {

    protected $table = 'inquiry_sub';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'creator_id' => 'string',
        'created_by'=>'string',
        'updated_by'=>'string',
        'modifier_id' => 'string',
        'auditor_id' => 'string',
        'decider' => 'string',
        'opener' => 'string',
        'cfm_id' => 'string',
        'min_supplier_id' => 'string',
        'max_supplier_id' => 'string',
    ];

}
