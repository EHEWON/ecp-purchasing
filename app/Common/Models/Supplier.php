<?php

namespace App\Common\Models;

class Supplier extends BaseModel {

    protected $table = 'supplier';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STATUS_DRAFT = 'DRAFT'; // 临时供应商
    const STATUS_REVIEW = 'REVIEW';  // 待审核
    const STATUS_APPROVING = 'APPROVING';  // 审核中
    const STATUS_APPROVED = 'APPROVED';  // 审核通过
    const STATUS_INVALID = 'INVALID';  // 驳回
    const STATUS_CLOSED = 'CLOSED';  // 已关闭
    const STATUS_UNOPENED = 'UNOPENED';  // 未开启
    const AUDIT_STATUS_AUDIT = 'REVIEW'; //  '审核中';
    const AUDIT_STATUS_REJECTED = 'REJECTED'; //'审核拒绝';
    const AUDIT_STATUS_PASS = 'PASS'; // '审核通过';
    const AUDIT_STATUS_DRAFT = 'DRAFT'; // '草稿';
    protected $casts = [
        'purchaser_id' => 'string',
        'supplier_group_id' => 'string',
        'disabled_by' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'checked_by' => 'string',
    ];

}
