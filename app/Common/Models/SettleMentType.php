<?php

namespace App\Common\Models;

class SettleMentType extends BaseModel {

    protected $table = 'settle_ment_type';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
