<?php

namespace App\Common\Models\Project;

use App\Common\Models\BaseModel;

class ProjectInvitationEntry extends BaseModel {

    protected $table = 'project_invitation_entry';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
