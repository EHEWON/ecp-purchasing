<?php

namespace App\Common\Models;

class InvoiceType extends BaseModel {

    protected $table = 'invoice_type';
    protected $primaryKey = 'id';
    protected $casts = [
        'group_id' => 'string',
        'master_id' => 'string',
    ];
    protected $keyType = 'string';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
