<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\BidBill\BidBillPayRepo;
use Illuminate\Http\Request;

class BidBillPayService extends Service {

    protected $guard = 'admin';
    public $middleware = [
//        UserTypeMiddleware::class => ['only' => ['add', 'edited', 'enable', 'disable', 'delete']],
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
        return (new BidBillPayRepo)->payList($request);
    }

    /**
     * 人员类型列表
     * @param int $id
     */
    public function info(int $id) {
        return (new BidBillPayRepo)->info($id);
    }

    /**
     * 人员类型列表
     * @param int $id
     * @param Request $request
     */
    public function payAudit(int $id, Request $request) {
        return (new BidBillPayRepo)->payAudit($id, $request);
    }

    /**
     * 人员类型列表
     * @param int $id
     * @param Request $request
     */
    public function returnAudit(int $id, Request $request) {
        return (new BidBillPayRepo)->returnAudit($id, $request);
    }

}
