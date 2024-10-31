<?php

namespace App\Common\Models\Quote;

use App\Common\Models\BaseModel;

class QuoteEntry extends BaseModel {

    protected $table = 'quote_entry';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'quote_id' => 'string',
        'material_id' => 'string',
        'asst_pro_id' => 'string',
        'unit_id' => 'string',
        'deli_type_id' => 'string',
        'req_org_id' => 'string',
        'created_by'=> 'string',
        'pur_org_id' => 'string',
        'rcv_org_id' => 'string',
        'inquiry_entry_id'=> 'string',
        'settle_org_id' => 'string',
        'pay_org_id' => 'string',
        'project_id' => 'string',
        'trace_id' => 'string',
        'tax_rate_id' => 'string',
        'quote_unit_id' => 'string',
        'inquiry_unit_id' => 'string',
        'line_type_id' => 'string',
        'boss_goods_id' => 'string',
    ];

}
