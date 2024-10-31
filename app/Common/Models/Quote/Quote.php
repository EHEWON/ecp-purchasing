<?php

namespace App\Common\Models\Quote;

use App\Common\Models\BaseModel;

class Quote extends BaseModel {

    protected $table = 'quote';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'inquiry_id' => 'string',
        'req_org_id' => 'string',
        'org_id' => 'string',
        'rcv_org_id' => 'string',
        'settle_org_id' => 'string',
        'payment_terms' => 'string',
        'settlement_method' => 'string',
        'pay_org_id' => 'string',
        'person_id' => 'string',
        'supplier_id' => 'string',
        'contacter_id' => 'string',
//        'pay_cond_id' => 'string',
        'settle_type_id' => 'string',
        'loc_curr_id' => 'string',
        'exch_type_id' => 'string',
        'biz_partner_id' => 'string',
        'bill_type_id' => 'string',
        'business_type_id' => 'string',
        'last_update_user_id' => 'string',
        'fk_erui_fktj' => 'string',
        'fk_erui_jsfs' => 'string',
    ];

}
