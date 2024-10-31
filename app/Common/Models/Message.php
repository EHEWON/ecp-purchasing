<?php

namespace App\Common\Models;

class Message extends BaseModel {

    protected $table = 'message';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'sender_id' => 'string',
        'org_id' => 'string'
    ];

}
