<?php

namespace App\Common\Models;

class SupplierEvaGrade extends BaseModel {

    protected $table = 'supplier_eva_grade';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
