<?php

namespace App\Common\Models\Inquiry;

use App\Common\Models\BaseModel;

class EntrySub extends BaseModel {

    protected $table = 'inquiry_entry_sub';
    protected $primaryKey = 'entry_id';
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
        'goods_id' => 'string',
        'basic_unit_id' => 'string',
        'asst_unit_id' => 'string',
        'po_bill_id' => 'string',
        'po_entry_id' => 'string',
        'pc_bill_id' => 'string',
        'pc_entry_id' => 'string',
        'src_bill_id' => 'string',
        'src_entry_id' => 'string',
        'pr_bill_id' => 'string',
        'pr_entry_id' => 'string',
    ];

}
