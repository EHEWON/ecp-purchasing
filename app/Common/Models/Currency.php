<?php

namespace App\Common\Models;

class Currency extends BaseModel {

    protected $table = 'currency';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
