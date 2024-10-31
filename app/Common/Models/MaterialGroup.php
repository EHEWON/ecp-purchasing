<?php

namespace App\Common\Models;

class MaterialGroup extends BaseModel {

    protected $table = 'material_group';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
