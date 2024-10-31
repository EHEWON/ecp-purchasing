<?php

namespace App\Common\Models;

class SupplierContact extends BaseModel {

    protected $table = 'supplier_contact';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'supplier_id' => 'string',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
