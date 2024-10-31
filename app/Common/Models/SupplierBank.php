<?php

namespace App\Common\Models;

class SupplierBank extends BaseModel {

    protected $table = 'supplier_bank';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'supplier_id' => 'string',
        'currency_id' => 'string',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
