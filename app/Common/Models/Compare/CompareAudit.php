<?php

namespace App\Common\Models\Compare;

use App\Common\Models\BaseModel;

class CompareAudit extends BaseModel {

    protected $table = 'compare_audit';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'id' => 'string',
        'compare_id' => 'string',
        'user_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

}
