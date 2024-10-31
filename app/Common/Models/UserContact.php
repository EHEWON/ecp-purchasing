<?php

namespace App\Common\Models;

class UserContact extends BaseModel {

    protected $table = 'user_contact';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $keyType = 'string';
    protected $casts = [
        'user_id' => 'string',
    ];
    protected $escapeWhenCastingToString = true;

}
