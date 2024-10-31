<?php

namespace App\Common\Models;

class SupplierGrade extends BaseModel {

    protected $table = 'supplier_grade';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    

}
