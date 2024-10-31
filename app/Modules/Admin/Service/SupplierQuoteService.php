<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\{
    Repository\Supplier\QuoteRepo,
    Repository\Supplier\QuoteEntryRepo,
    Middleware\SupplierQuoteMiddleware,
    Middleware\SupplierQuoteEntryMiddleware
};
use Illuminate\Http\Request;

class SupplierQuoteService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        SupplierQuoteMiddleware::class => ['only' => ['add', 'edited']],
        SupplierQuoteEntryMiddleware::class => ['only' => ['add', 'edited']],
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
        return (new QuoteRepo)->getList($request);
    }

    public function export(Request $request) {
        return (new QuoteRepo)->export($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new QuoteRepo)->info($id);
    }

    public function infoByInquiryId($inquiryId) {
        return (new QuoteRepo)->infoByInquiryId($inquiryId);
    }

    public function listByInquiryId($inquiryId) {
        return (new QuoteRepo)->listByInquiryId($inquiryId);
    }

    public function add(Request $request) {
        return (new QuoteRepo)->add($request);
    }

    public function delete(Request $request) {
        return (new QuoteRepo)->deleteData($request);
    }

    public function edited(Request $request) {
        return (new QuoteRepo)->edited($request->id, $request);
    }

    public function entryExport($inquiryId, Request $request) {
        return (new QuoteEntryRepo)->export($inquiryId, $request);
    }

    public function entryImport(Request $request) {
        return (new QuoteEntryRepo)->import($request);
    }

}
