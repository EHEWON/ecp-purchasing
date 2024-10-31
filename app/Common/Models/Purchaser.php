<?php

namespace App\Common\Models;

class Purchaser extends BaseModel {

    protected $table = 'purchaser';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $casts = [
        'parent_id' => 'string',
        'city_id' => 'string',
        'province_id' => 'string',
        'bottom_id' => 'string',
    ];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
