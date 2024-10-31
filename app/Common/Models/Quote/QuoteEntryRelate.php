<?php

namespace App\Common\Models\Quote;

use App\Common\Models\BaseModel;

class QuoteEntryRelate extends BaseModel {

    protected $table = 'quote_entry_relate';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'entry_id' => 'string',
        'stable_id' => 'string',
        'sbill_id' => 'string',
        's_id' => 'string',
        'qty_old' => 'string',
    ];

}
