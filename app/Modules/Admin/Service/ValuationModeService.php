<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\ValuationModeRepo;
use Illuminate\Http\Request;

class ValuationModeService extends Service {

    protected $guard = 'admin';
    public $middleware = [];
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
     * 计价模式
     * @param Request $request
     */
    public function getAll() {
        return (new ValuationModeRepo)->getAll();
    }

    public function getList(Request $request) {
        return (new ValuationModeRepo)->getList($request);
    }

}
