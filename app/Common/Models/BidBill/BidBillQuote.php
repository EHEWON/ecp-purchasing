<?php

namespace App\Common\Models\BidBill;

use App\Common\Models\BaseModel;

class BidBillQuote extends BaseModel {

    protected $table = 'bid_bill_quote';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'bid_bill_id' => 'string',
        'supplier_id' => 'string',
        'quo_currency_id' => 'string',
        'quo_tax_rate_id' => 'string',
    ];

}
