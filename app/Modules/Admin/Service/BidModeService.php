<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\BidModeRepo;
use Illuminate\Http\Request;

class BidModeService extends Service {

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
     * 采购方式
     * @param Request $request
     */
    public function getAll() {
        return (new BidModeRepo)->getAll();
    }

    public function getList(Request $request) {
        return (new BidModeRepo)->getList($request);
    }

}
