<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\SettingRepo,
    Middleware\SettingMiddleware
};
use Illuminate\Http\Request;

class SettingService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        SettingMiddleware::class => ['only' => ['updateOrAdd', 'delete']],
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
    public function getList(Request $request) {
        return (new SettingRepo)->getList($request);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function updateOrAdd(Request $request) {
        return (new SettingRepo)->updateOrAdd($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new SettingRepo)->deleteData($request);
    }

}
