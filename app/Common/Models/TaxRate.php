<?php

namespace App\Common\Models;

class TaxRate extends BaseModel {

    protected $table = 'tax_rate';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
