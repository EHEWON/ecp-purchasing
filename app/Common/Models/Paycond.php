<?php

namespace App\Common\Models;

class Paycond extends BaseModel {

    protected $table = 'paycond';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string'
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
