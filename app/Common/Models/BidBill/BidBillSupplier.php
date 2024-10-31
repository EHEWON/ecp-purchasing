<?php

namespace App\Common\Models\BidBill;

use App\Common\Models\BaseModel;

class BidBillSupplier extends BaseModel {

    protected $table = 'bid_bill_supplier';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'bid_bill_id' => 'string',
        'supplier_id' => 'string',
        'enroll_id' => 'string',
        'audit_id' => 'string',
        'pay_id' => 'string',
        'return_id' => 'string',
        'allow_bid' => 'string',
    ];

}
