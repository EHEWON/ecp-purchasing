<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\TemplateTypeRepo;
use Illuminate\Http\Request;

class TemplateTypeService extends Service {

    protected $guard = 'admin';
    public $middleware = [
    ];
    public $beforeEvent = [];
    public $afterEvent = [
    ];

    public function getRules() {
        return [
        ];
    }

    public function getMessages() {
        return [
        ];
    }

    protected $model;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getAll(Request $request) {
        return (new TemplateTypeRepo)->getAll($request);
    }

}
