<?php

namespace App\Common\Models;

class Division extends BaseModel {

    protected $table = 'division';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'country_id' => 'string',
        'divisionlv_id' => 'string',
        'parent_id' => 'string',
        'creator_id' => 'string',
        'modifier_id' => 'string',
        'disabler_id' => 'string',
        'master_id' => 'string',
    ];
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
