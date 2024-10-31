<?php

namespace App\Common\Models;

class Notice extends BaseModel {

    protected $table = 'notice';
    protected $primaryKey = 'id';
    protected $casts = [
        'biz_partner_id' => 'string',
        'bill_type_id' => 'string',
        'org_id' => 'string',
        'notice_tpl_id' => 'string',
        'updated_by' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'bill_date';
    const UPDATED_AT = 'updated_at';

}
