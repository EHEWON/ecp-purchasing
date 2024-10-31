<?php

namespace App\Common\Models\BidBill;

use App\Common\Models\BaseModel;

class BidBill extends BaseModel {

    protected $table = 'bid_bill';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'bill_date';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'person_id' => 'string',
        'org_id' => 'string',
        'pay_cond_id' => 'string',
        'settle_type_id' => 'string',
        'curr_id' => 'string', //计价模式
        'biz_partner_id' => 'string',
        'bill_type_id' => 'string',
//        'pay_cond_id' => 'string',
        'pur_officer' => 'string',
        'business_type_id' => 'string',
        'updated_by' => 'string',
    ];

}
