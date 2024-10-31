<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\Supplier\ProjectRepo;
use Illuminate\Http\Request;

class SupplierProjectService extends Service {

    protected $guard = 'admin';
    public $middleware = [ ];
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
     * 列表
     * @param Request $request
     */
    public function getList(Request $request) {
        return (new ProjectRepo)->getList($request);
    }

    /**
     * 信息
     * @return
     */
    public function info($id) {
        return (new ProjectRepo)->info($id);
    }

    /**
     * 信息
     * @return
     */
    public function noticeInfo($id) {
        return (new ProjectRepo)->noticeInfo($id);
    }

    /**
     * 信息
     * @return
     */
    public function cmfInfo($id) {
        return (new ProjectRepo)->cmfInfo($id);
    }

    /**
     * 报名参加
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function signUp(int $id, Request $request) {
        return (new ProjectRepo)->signUp($id, $request);
    }

    /**
     * 不报名参加
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function unSignUp(int $id, Request $request) {
        return (new ProjectRepo)->unSignUp($id, $request);
    }

    /**
     * 不报名参加
     * @param int $id
     * @param Request $request
     * @return type
     */
    public function quote(int $id, Request $request) {
        return (new ProjectRepo)->quote($id, $request);
    }

    public function quoteedited(int $id, Request $request) {
        return (new ProjectRepo)->quoteEdited($id, $request);
    }

    public function quoteinfo($id) {
        return (new ProjectRepo)->quoteinfo($id);
    }

    public function publishDownload($id) {
        return (new ProjectRepo)->publishDownload($id);
    }

    public function docDownload(string $group, $id, Request $request) {
        return (new ProjectRepo)->docDownload($group, $id,$request);
    }

    public function download(string $group, $id, Request $request) {
        return (new ProjectRepo)->download($id, $group,$request);
    }

}
