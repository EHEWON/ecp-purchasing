<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\RolesUserRepo;
use App\Modules\Admin\Middleware\RolesUserMiddleware;
use Illuminate\Http\Request;

class RolesUserService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        RolesUserMiddleware::class => ['only' => ['updateOrAdd']],
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
     * 修改人员类型
     * @return
     */
    public function updateOrAdd(Request $request) {
        return (new RolesUserRepo)->updateOrAdd($request);
    }

    public function delete(Request $request) {
        return (new RolesUserRepo)->deleteData($request);
    }

}
