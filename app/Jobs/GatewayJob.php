<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Jobs;

use \GatewayWorker\Lib\Gateway;
use Exception;

class GatewayJob extends Job {

    protected $response;
    protected $request;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $type) {
        $this->request = $request;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        switch ($this->type) {
            case 'wsSendMsg':
                return $this->wsSendMsg($this->request);
            case 'bindUid':
                return $this->bindUid($this->request);
            case 'offline':
                return $this->offline($this->request);
            case 'doBindUid':
                return $this->wsSendMsg($this->request);
            case 'bindGroup':
                return $this->bindGroup($this->request);
        }
    }

    /**
     * 消息发送
     * @param type $request
     * @return type
     */
    function wsSendMsg($request) {
        $user = $request['user'];
        $type = $request['type'];
        $data = $request['data'];
        $message = json_encode([
            'type' => $type,
            'time' => date('Y-m-d H:i:s'),
            'data' => $data
        ]);
        try {
            Gateway::$registerAddress = '127.0.0.1:' . env('WORKER_REG_PORT', 1892);
            $count = Gateway::getClientCountByGroup($user);
            if ($count === 0) {
                return;
            }
            Gateway::sendToGroup($user, $message);
        } catch (Exception $ex) {
            
        }
    }

    /**
     * 将用户UId绑定到消息推送服务中
     * @return \think\response\Json
     */
    function bindUid($input) {
        $this->doBindUid($input);
        return true;
    }

    // 执行绑定
    function doBindUid($input) {
        $userId = $input['user_id'];
        $clientId = $input['client_id'];
        $bidBillId = $input['bid_bill_id'];
        // 如果当前ID在线，将其他地方登陆挤兑下线
        if (Gateway::isUidOnline($userId)) {
            $this->wsSendMsg(['user' => $bidBillId, 'type' => 'offline', 'data' => ['id' => $userId, 'client_id' => $clientId]]);
        }
        Gateway::bindUid($clientId, $userId);
        // 查询团队，如果有团队则加入团队
        if ($bidBillId) {
            Gateway::$registerAddress = '127.0.0.1:' . env('WORKER_REG_PORT', 1892);
            Gateway::joinGroup($clientId, $bidBillId);
        }
        $this->wsSendMsg(['user' => $bidBillId, 'type' => 'isOnline', 'data' => ['id' => $userId, 'is_online' => 1]]);
    }

    /**
     * 下架
     * @param type $input
     */
    function offline($input) {
        $userId = $input['user_id'];
        $clientId = $input['client_id'];
        $bidBillId = $input['bid_bill_id'];
        Gateway::unbindUid($clientId, $userId);
        Gateway::closeClient($clientId);
        $this->wsSendMsg(['user' => $bidBillId, 'type' => 'offline', 'data' => ['id' => $userId, 'is_online' => 0]]);
    }

    /**
     * 绑定组织
     * @param type $input
     * @return boolean
     */
    function bindGroup($input) {
        $clientId = $input['client_id'];
        $groupId = $input['bid_bill_id'];
        Gateway::$registerAddress = '127.0.0.1:' . env('WORKER_REG_PORT', 1892);
        Gateway::joinGroup($clientId, $groupId);
        return true;
    }

}
