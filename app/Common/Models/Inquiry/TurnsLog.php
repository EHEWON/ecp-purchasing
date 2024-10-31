<?php

namespace App\Common\Models\Inquiry;

use App\Common\Models\BaseModel;

class TurnsLog extends BaseModel {

    protected $table = 'inquiry_turns_log';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'handler_id' => 'string',
        'entry_id' => 'string',
    ];

}
