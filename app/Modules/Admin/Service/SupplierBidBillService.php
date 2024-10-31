<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Supplier\{
    BidBillRepo,
    BidBillPayRepo,
    BidBillHallRepo,
    BidBillQuoteRepo
};
use Illuminate\Http\Request;

class SupplierBidBillService extends Service {

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
        return (new BidBillRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new BidBillRepo)->info($id);
    }

    public function pays(Request $request) {
        return (new BidBillPayRepo)->getList($request);
    }

    public function pay(int $id, Request $request) {
        return (new BidBillPayRepo)->updateData($id, $request);
    }

    public function payInfo(int $id, Request $request) {
        return (new BidBillPayRepo)->info($id, $request);
    }

    public function hallInfo(int $id) {
        return (new BidBillHallRepo)->hallInfo($id);
    }

    public function hall(Request $request) {
        return (new BidBillHallRepo)->getList($request);
    }

    public function quote(Request $request) {
        return (new BidBillQuoteRepo)->updateData($request->id, $request);
    }

    public function bindUid(int $id, Request $request) {
        return (new BidBillQuoteRepo)->bindGroup($id, $request);
    }

    public function bindGroup(int $id, Request $request) {
        return (new BidBillQuoteRepo)->bindGroup($id, $request);
    }

    public function offline(int $id, Request $request) {
        return (new BidBillQuoteRepo)->offline($id, $request);
    }

    /**
     * 报名参加竞价
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function signUp(int $id, Request $request) {
        return (new BidBillRepo)->signUp($id, $request);
    }

    /**
     * 不报名参加竞价
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function unSignUp(int $id, Request $request) {
        return (new BidBillRepo)->unSignUp($id, $request);
    }

}
