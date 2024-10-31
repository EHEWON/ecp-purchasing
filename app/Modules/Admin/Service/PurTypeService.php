<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\PurTypeRepo;
use Illuminate\Http\Request;

class PurTypeService extends Service {

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
     * 采购类型
     * @param Request $request
     */
    public function getAll() {
        return (new PurTypeRepo)->getAll();
    }

    public function getList(Request $request) {
        return (new PurTypeRepo)->getList($request);
    }

}
