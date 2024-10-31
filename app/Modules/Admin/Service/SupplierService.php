<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Common\Models\UserSupplier;
use App\Modules\Admin\Middleware\{
    SupplierMiddleware,
    SupplierAttachMiddleware,
    SupplierBankMiddleware,
    SupplierContactMiddleware
};
use App\Modules\Admin\Repository\{
    Supplier\NoticeRepo,
    SupplierRepo,
    SupplierContactRepo
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SupplierService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        SupplierMiddleware::class => ['only' => ['add',
                'edited',
                'enable',
                'disable',
                'delete',
                'register',
                'phoneEmail']],
        SupplierAttachMiddleware::class => ['only' => ['add', 'edited']],
        SupplierBankMiddleware::class => ['only' => ['add', 'edited']],
        SupplierContactMiddleware::class => ['only' => ['add', 'edited']],
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
    public function register(Request $request) {
        return (new SupplierRepo)->register($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function phoneEmail(Request $request) {
        return (new SupplierRepo)->phoneEmail($request);
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new SupplierRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id, Request $request) {
        return (new SupplierRepo)->info($id, $request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function manage() {
        return (new SupplierRepo)->manage();
    }

    /**
     * 修改人员类型
     * @return
     */
    public function edited(Request $request) {
        return (new SupplierRepo)->edited($request->id, $request);
    }

    /**
     * 修改人员类型
     * @return
     */
    public function company(Request $request) {
        return (new SupplierRepo)->company($request);
    }

    public function enterpriseType() {
        return (new SupplierRepo)->getEnterpriseList();
    }

    public function notice(Request $request) {
        return (new NoticeRepo)->getList($request);
    }

    public function noticeInfo($noticeId) {
        return (new NoticeRepo)->info($noticeId);
    }

    public function noticeDelete(Request $request) {
        return (new NoticeRepo)->deleteData($request);
    }

    public function noticeRead(Request $request) {
        return (new NoticeRepo)->read($request);
    }

    public function noticeUnread(Request $request) {
        return (new NoticeRepo)->unread($request);
    }

    public function defaultContact() {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        return (new SupplierContactRepo)->getDefaultContact($supplierId);
    }

}
