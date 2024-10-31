<?php

namespace App\Common\Models;

class DivisionLevel extends BaseModel {

    protected $table = 'division_level';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'country_id' => 'string',
        'divisionlv_id' => 'string',
        'parent_id' => 'string',
        'creator_id' => 'string',
        'modifier_id' => 'string',
        'disabler_id' => 'string',
        'master_id' => 'string',
    ];

}
