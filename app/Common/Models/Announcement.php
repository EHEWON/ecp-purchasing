<?php

namespace App\Common\Models;

class Announcement extends BaseModel {

    protected $table = 'announcement';
    protected $primaryKey = 'id';
    protected $casts = [
        'creator_id' => 'string',
        'modifier_id' => 'string',
        'org_id' => 'string',
        'auditor_id' => 'string',
        'publisher' => 'string',
        'pur_type_id' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
