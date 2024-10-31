<?php

namespace App\Common\Models;

class Country extends BaseModel {

    protected $table = 'country';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
