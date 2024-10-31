<?php

namespace App\Common\Models\Compare;

use App\Common\Models\BaseModel;

class Compare extends BaseModel {

    protected $table = 'compare';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'org_id' => 'string',
        'req_org_id' => 'string',
        'payment_terms' => 'string',
        'rcv_org_id' => 'string',
        'settle_org_id' => 'string',
        'pay_org_id' => 'string',
        'person_id' => 'string',
        'pay_cond_id' => 'string',
        'settle_type_id' => 'string',
        'curr_id' => 'string',
        'loc_curr_id' => 'string',
        'exch_type_id' => 'string',
        'biz_partner_id' => 'string',
        'bill_type_id' => 'string',
        'business_type_id' => 'string',
        'last_update_user_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'audit_by' => 'string',
    ];

}
