<?php

namespace App\Common\Models;

class SupplierAudit extends BaseModel {

    protected $table = 'supplier_audit';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $casts = [
        'supplier_id' => 'string',
        'user_id' => 'string',
    ];
    const STATUS_PENDING = 'REVIEW'; // 待审核
    const STATUS_PASS = 'PASS'; // 审核通过
    const STATUS_REJECTED = 'REJECTED'; // 审核拒绝
    const STATUS_DRAFT = 'DRAFT'; // 草稿状态，用于预览

}
