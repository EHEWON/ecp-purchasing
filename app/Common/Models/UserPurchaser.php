<?php

namespace App\Common\Models;

class UserPurchaser extends BaseModel {

    protected $table = 'user_purchaser';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'user_id' => 'string',
        'purchaser_id' => 'string',
        'bot_purchaser_id' => 'string',
    ];
    protected $keyType = 'string';

}
