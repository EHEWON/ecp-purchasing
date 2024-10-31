<?php

namespace App\Common\Models;

use App\Common\Models\Menus;

class RoleUsers extends BaseModel {

    protected $table = 'role_user';
    protected $primaryKey = 'role_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $keyType = 'string';
    protected $casts = [
        'role_id' => 'string',
        'content_id' => 'string',
        'team_id' => 'string',
        'user_id' => 'string',
    ];
    protected $escapeWhenCastingToString = true;
    //JSON 隐藏
    protected $hidden = [
        'deleted_flag',
    ];
    protected $fillable = [
        'role_id'
    ];

    public function menus() {
        return $this->belongsToMany(Menus::class, 'role_has_menus', 'role_id', 'menu_id')
                        ->where(['deleted_flag' => 'N', 'status' => 'NORMAL'])
                        ->orderBy('sort', 'ASC');
    }

}
