<?php

namespace App\Common\Models;

class UserSupplier extends BaseModel {

    protected $table = 'user_supplier';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'user_id' => 'string',
        'supplier_id' => 'string',
    ];
    protected $keyType = 'string';

}
