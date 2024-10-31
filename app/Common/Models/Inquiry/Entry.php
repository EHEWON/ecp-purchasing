<?php

namespace App\Common\Models\Inquiry;

use App\Common\Models\BaseModel;

class Entry extends BaseModel {

    protected $table = 'inquiry_entry';
    protected $primaryKey = 'entry_id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'id' => 'string',
        'material_id' => 'string',
        'asstpro_id' => 'string',
        'unit_id' => 'string',
        'deli_type_id' => 'string',
        'req_org_id' => 'string',
        'pur_org_id' => 'string',
        'rcv_org_id' => 'string',
        'settle_org_id' => 'string',
        'pay_org_id' => 'string',
        'project_id' => 'string',
        'trace_id' => 'string',
        'tax_rate_id' => 'string',
        'inquiry_unit_id' => 'string',
        'quote_unit_id' => 'string',
        'supplier_id' => 'string',
        'new_tax_rate_id' => 'string',
        'line_type_id' => 'string',
    ];

}
