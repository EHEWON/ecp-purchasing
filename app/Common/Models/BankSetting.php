<?php

namespace App\Common\Models;

class BankSetting extends BaseModel {

    protected $table = 'bank_setting';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'creator_id' => 'string',
        'modifier_id' => 'string',
        'parent_id' => 'string',
    ];
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
