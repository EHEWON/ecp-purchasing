<?php

namespace App\Common\Models;

class NoticeSupplier extends BaseModel {

    protected $table = 'notice_supplier';
    protected $primaryKey = 'id';
    protected $casts = [
        'notice_id' => 'string',
        'supplier_id' => 'string',
    ];
    protected $keyType = 'string';
    protected $escapeWhenCastingToString = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
