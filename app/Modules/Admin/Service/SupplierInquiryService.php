<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Supplier\{
    InquiryRepo,
    InquiryExportRepo
};
use Illuminate\Http\Request;

class SupplierInquiryService extends Service {

    protected $guard = 'admin';
    public $middleware = [
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
        return (new InquiryRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new InquiryRepo)->info($id);
    }

    /**
     * 不报价
     * @return
     */
    public function quote(int $id) {
        return (new InquiryRepo)->quote($id);
    }

    /**
     * 报价
     * @return
     */
    public function unQuote(int $id) {
        return (new InquiryRepo)->unQuote($id);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function export(Request $request) {
        return (new InquiryExportRepo)->export($request);
    }

}
