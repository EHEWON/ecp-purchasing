<?php


namespace App\Common\Models;

class QuickMenus extends BaseModel {

    protected $table = 'quick_menus';
    protected $primaryKey = 'menu_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'created_by'
    ];

}
