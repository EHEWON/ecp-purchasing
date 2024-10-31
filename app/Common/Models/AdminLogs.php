<?php

namespace App\Common\Models;

class AdminLogs extends BaseModel {

    protected $table = "admin_logs";
    protected $primaryKey = 'log_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * 隐藏字段
     * @var type 
     */
    protected $hidden = [
        'deleted_flag',
    ];

    /**
     * 填充
     * @var type 
     */
    protected $fillable = [
        'log_id'
    ];

}
