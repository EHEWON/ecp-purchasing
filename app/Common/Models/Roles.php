<?php

namespace App\Common\Models;

use App\Common\Models\Menus;

class Roles extends BaseModel {

    protected $table = 'roles';
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $keyType = 'string';
    //JSON 隐藏
    protected $hidden = [
        'deleted_flag',
    ];
    protected $fillable = [
        'role_id',
    ];
    protected $casts = [
        'role_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'team_id' => 'string',
    ];

    public function menus() {
        return $this->belongsToMany(Menus::class, 'role_has_menus', 'role_id', 'menu_id')
                        ->where(['deleted_flag' => 'N', 'status' => 'NORMAL'])
                        ->orderBy('sort', 'ASC');
    }

}
