<?php

namespace App\Common\Models;

class Unit extends BaseModel {

    protected $table = 'unit';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
