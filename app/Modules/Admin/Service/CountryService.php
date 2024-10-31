<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\CountryRepo;
use App\Modules\Admin\Middleware\CountryMiddleware;
use Illuminate\Http\Request;

class CountryService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        CountryMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        $data = (new CountryRepo)->getList($request);
        return $data;
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new CountryRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new CountryRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new CountryRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new CountryRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new CountryRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new CountryRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new CountryRepo)->import($request);
    }

    public function export(Request $request) {
        return (new CountryRepo)->export($request);
    }

}
