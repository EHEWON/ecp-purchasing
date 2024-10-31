<?php

namespace App\Common\Models;

class TaxationSys extends BaseModel {

    protected $table = 'taxation_sys';
    protected $primaryKey = 'id';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
