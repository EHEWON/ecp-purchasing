<?php

namespace App\Common\Models\Quote;

use App\Common\Models\BaseModel;

class QuoteEntrySub extends BaseModel {

    protected $table = 'quote_entry_sub';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'quote_id' => 'string',
        'entry_id' => 'string',
        'goods_id' => 'string',
        'asst_unit_id' => 'string',
        'basic_unit_id' => 'string',
        'po_bill_id' => 'string',
        'po_entry_id' => 'string',
        'pc_bill_id' => 'string',
        'pc_entry_id' => 'string',
        'src_bill_id' => 'string',
        'src_entry_id' => 'string',
        'pr_bill_id' => 'string',
        'pr_entry_id' => 'string',
        'cfm_tax_rate_id' => 'string',
    ];

}
