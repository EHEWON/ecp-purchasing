<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Project\ProjectOpenRepo;
use Illuminate\Http\Request;

class ProjectOpenService extends Service {

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
     * 缴费信息
     * @return
     */
    public function info($id) {
        return (new ProjectOpenRepo)->info($id);
    }

    /**
     * 缴费信息
     * @return
     */
    public function edited(Request $request) {
        return (new ProjectOpenRepo)->edited($request->id, $request);
    }

}
