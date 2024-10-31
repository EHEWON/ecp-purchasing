<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\PurProjectRepo;
use Illuminate\Http\Request;

class PurProjectService extends Service {

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
     * 采购项目
     * @param Request $request
     */
    public function getAll() {
        return (new PurProjectRepo)->getAll();
    }

    public function getList(Request $request) {
        return (new PurProjectRepo)->getList($request);
    }

}
