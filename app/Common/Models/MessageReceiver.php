<?php

namespace App\Common\Models;

class MessageReceiver extends BaseModel {

    protected $table = 'message_receiver';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'message_id' => 'string',
        'receiver_id' => 'string',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
