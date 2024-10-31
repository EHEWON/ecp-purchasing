<?php

namespace App\Common\Models;

class PurProject extends BaseModel {

    protected $table = 'pur_project';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'org_id' => 'string',
    ];

}
