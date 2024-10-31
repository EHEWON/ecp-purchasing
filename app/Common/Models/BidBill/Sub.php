<?php

namespace App\Common\Models\BidBill;

use App\Common\Models\BaseModel;

class Sub extends BaseModel {

    protected $table = 'bid_bill_sub';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'auditor_by' => 'string',
        'cfm_id' => 'string',
        'terminate_by' => 'string',
        'finished_by' => 'string',
        'paused_by' => 'string',
        'decider_by' => 'string',
        'bid_bill_id' => 'string',
        'supplier_id' => 'string', //计价模式
        'supplier_id1' => 'string',
    ];

}
