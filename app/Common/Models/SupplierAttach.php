<?php

namespace App\Common\Models;

class SupplierAttach extends BaseModel {

    protected $table = 'supplier_attach';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'supplier_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string'
    ];

}
