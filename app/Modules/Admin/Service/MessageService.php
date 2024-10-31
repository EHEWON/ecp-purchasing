<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Modules\Admin\Repository\MessageRepo;
use Illuminate\Http\Request;

class MessageService extends Service {

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
        return (new MessageRepo)->getList($request);
    }

    /**
     * 人员类型信息
     * @return
     */
    public function info($id) {
        return (new MessageRepo)->info($id);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function delete(Request $request) {
        return (new MessageRepo)->deleteData($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function read(Request $request) {
        return (new MessageRepo)->read($request);
    }

    /**
     * 新增人员类型
     * @return
     */
    public function unread(Request $request) {
        return (new MessageRepo)->unread($request);
    }
    
    
    public function notReadCount(Request $request) {
        return (new MessageRepo)->notReadCount($request);
    }
    

}
