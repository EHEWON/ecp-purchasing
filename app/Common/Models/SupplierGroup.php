<?php

namespace App\Common\Models;

class SupplierGroup extends BaseModel {

    protected $table = 'supplier_group';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
