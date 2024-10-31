<?php

namespace App\Common\Models\BidBill;

use App\Common\Models\BaseModel;

class BidBillPay extends BaseModel {

    protected $table = 'bid_bill_pay';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'bid_bill_id' => 'string',
        'org_id' => 'string',
        'supplier_id' => 'string',
        'bid_entry_id' => 'string',
        'created_by' => 'string', //计价模式
        'updated_by' => 'string',
        'audited_by' => 'string'
    ];

}
