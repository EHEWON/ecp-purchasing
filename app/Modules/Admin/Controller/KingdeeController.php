<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\Controller;
use App\Modules\Admin\Admin;
use Illuminate\Http\Request;

class KingdeeController extends Controller {

    public function getRules() {
        return [];
    }

    /**
     *
     * @desc 询单和招标
     *
     * @param Request $request Description
     */
    public function getList(Request $request) {
        return Admin::service('KingdeeService')->getList($request);
    }

    /**
     *
     * @desc 获取询单公告
     *
     * @param Request $request Description
     */
    public function noticeList(Request $request) {
        return Admin::service('KingdeeService')->noticeList($request);
    }

    /**
     *
     * @desc 获取询单公告详情
     *
     * @param string $id Description
     */
    public function noticeDetail(string $id) {
        return Admin::service('KingdeeService')->noticeDetail($id);
    }

    /**
     *
     * @desc 招标详情
     *
     * @param string $id Description
     */
    public function bidDetail(string $id) {
        return Admin::service('KingdeeService')->bidDetail($id);
    }

    /**
     *
     * @desc 获取招标公告
     *
     * @param Request $request Description
     */
    public function announcementList(Request $request) {
        return Admin::service('KingdeeService')->announcementList($request);
    }

    /**
     *
     * @desc 获取招标公告详情
     *
     * @param string $id Description
     */
    public function announcementDetail(string $id) {
        return Admin::service('KingdeeService')->announcementDetail($id);
    }

    /**
     *
     * @desc 获取询单公告
     *
     * @param Request $request Description
     */
    public function search(Request $request) {
        return Admin::service('KingdeeService')->search($request);
    }

    /**
     *
     * @desc 获取询单公告
     *
     * @param Request $request Description
     */
    public function groupList(Request $request) {
        return Admin::service('KingdeeService')->groupList($request);
    }

}
