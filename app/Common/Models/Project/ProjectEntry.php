<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectEntry extends BaseModel {

    protected $table = 'project_entry';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'modify_time';
    protected $casts = [
        'project_section_id' => 'string',
        'entry_id' => 'string',
        'pur_project_id' => 'string',
        'tax_rate_id' => 'string',
        'material_id' => 'string',
        'cqprog_con_id' => 'string', //计价模式
        'fk_erui_wlmc' => 'string',
    ];

}
