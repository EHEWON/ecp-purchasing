<?php

namespace App\Common\Models\Quote;

use App\Common\Models\BaseModel;

class QuoteSub extends BaseModel {

    protected $table = 'quote_sub';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'quote_id' => 'string',
        'creator_id' => 'string',
        'modifier_id' => 'string',
        'auditor_id' => 'string',
        'cfm_id' => 'string',
    ];

}
