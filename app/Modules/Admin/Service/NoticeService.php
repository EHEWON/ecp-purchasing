<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\NoticeRepo;
use Illuminate\Http\Request;

class NoticeService extends Service {

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
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new NoticeRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new NoticeRepo)->info($id);
    }

}