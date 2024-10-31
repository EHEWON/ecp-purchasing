<?php

namespace App\Common\Models;

class NoticeUser extends BaseModel {

    protected $table = 'notice_user';
    protected $primaryKey = 'id';
    protected $casts = [
        'notice_id' => 'string',
        'user_id' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
