<?php

namespace App\Common\Models;

class SendLog extends BaseModel {

    protected $table = 'send_log';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
