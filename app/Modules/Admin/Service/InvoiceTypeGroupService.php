<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\InvoiceTypeGroupRepo;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class InvoiceTypeGroupService extends Service {

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
        return (new InvoiceTypeGroupRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new InvoiceTypeGroupRepo)->info($id);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new InvoiceTypeGroupRepo)->edited($request->id, $request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function add(Request $request) {
        return (new InvoiceTypeGroupRepo)->add($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function enable(Request $request) {
        return (new InvoiceTypeGroupRepo)->enable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function disable(Request $request) {
        return (new InvoiceTypeGroupRepo)->disable($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new InvoiceTypeGroupRepo)->deleteData($request);
    }

    public function import(Request $request) {
        return (new InvoiceTypeGroupRepo)->import($request);
    }

    public function export(Request $request) {
        return (new InvoiceTypeGroupRepo)->export($request);
    }

    public function number() {
        $newNumber = null;
        $redisKey = 'ECP:INVOICE_TYPE:GROUP:NUMBER:' . date('Ymd');
        if (Redis::command('exists', [$redisKey])) {
            $newNumber = Redis::get($redisKey);
        }
        $number = (new InvoiceTypeGroupRepo)->getInvoiceTypeGroupNo($newNumber);
        Redis::set($redisKey, $number, 86400);
        return $number;
    }

}
