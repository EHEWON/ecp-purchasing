<?php

namespace App\Common\Models\BidBill;

use App\Common\Models\BaseModel;


class BidBillEntry extends BaseModel {

    protected $table = 'bid_bill_entry';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'entry_id' => 'string',
        'asstpro_id' => 'string',
        'bid_bill_id' => 'string',
        'material_id' => 'string',
        'material_unit_id' => 'string',
        'unit_id' => 'string',
        'line_type_id' => 'string',
        'win_supplier_id' => 'string', //计价模式
        'tax_rate_id' => 'string',
        'project_id' => 'string',
        'trace_id' => 'string',
    ];

}
