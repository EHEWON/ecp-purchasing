<?php

namespace App\Common\Models;

class Setting extends BaseModel {

    protected $table = 'setting';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
