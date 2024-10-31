<?php

namespace App\Common\Models\Quote;

use App\Common\Models\BaseModel;

class QuoteRelate extends BaseModel {

    protected $table = 'quote_relate';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'tbill_id' => 'string',
        'ttable_id' => 'string',
        't_id' => 'string',
        'sbill_id' => 'string',
        'stable_id' => 'string',
        's_id' => 'string',
    ];

}
