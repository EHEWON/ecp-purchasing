<?php

namespace App\Common\Models;

class Permissions extends BaseModel {

    protected $table = 'permissions';
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
