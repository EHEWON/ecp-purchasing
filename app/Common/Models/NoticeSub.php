<?php

namespace App\Common\Models;

class NoticeSub extends BaseModel {

    protected $table = 'notice_sub';
    protected $primaryKey = 'id';
    protected $casts = [
        'notice_id' => 'string',
        'creator_id' => 'string',
        'modifier_id' => 'string',
        'auditor_id' => 'string',
        'cfm_id' => 'string',
        'src_bill_id' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
