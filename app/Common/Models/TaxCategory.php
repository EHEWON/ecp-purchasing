<?php

namespace App\Common\Models;

class TaxCategory extends BaseModel {

    protected $table = 'tax_category';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';

}
