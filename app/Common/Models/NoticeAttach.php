<?php

namespace App\Common\Models;

class NoticeAttach extends BaseModel {

    protected $table = 'notice_attach';
    protected $primaryKey = 'id';
    protected $casts = [
        'notice_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
