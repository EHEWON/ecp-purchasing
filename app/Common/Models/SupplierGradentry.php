<?php

namespace App\Common\Models;

class SupplierGradentry extends BaseModel {

    protected $table = 'supplier_gradentry';
    protected $primaryKey = 'id';
    protected $casts = [
        'grade_id' => 'string',
        'eva_grade_id' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
