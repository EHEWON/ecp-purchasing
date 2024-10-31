<?php

namespace App\Common\Models;

class InvoiceTypeGroup extends BaseModel {

    protected $table = 'invoice_type_group';
    protected $primaryKey = 'id';
    protected $casts = [
        'master_id' => 'string',
    ];
    protected $keyType = 'string';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
