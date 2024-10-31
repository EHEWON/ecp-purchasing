<?php

namespace App\Common\Models;

class Menus extends BaseModel {

    protected $table = 'menus';
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    //JSON 隐藏
    protected $hidden = [
        'deleted_flag',
    ];
    protected $fillable = [
        'id'
    ];

}
