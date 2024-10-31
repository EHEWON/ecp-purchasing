<?php

namespace App\Workerman;

use \GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Log;

date_default_timezone_set("Asia/Shanghai");

class Events {

    // 当有客户端连接时，将client_id返回，让mvc框架判断当前uid并执行绑定
    public static function onConnect($client_id) {
        Log::channel('command')->info('onConnect:' . $client_id);
        Gateway::sendToClient($client_id, json_encode(array(
            'type' => 'init',
            'date' => date('Y-m-d H:i:s'),
            'client_id' => $client_id,
            'data' => new \stdClass()
        )));
    }

    /**
     * 有消息时
     * @param int $client_id
     * @param mixed $message
     */
    public static function onMessage($client_id, $message) {
        // 客户端传递的是json数据
        $messageData = json_decode($message, true);
        Log::channel('command')->info('onMessage:client_id:' . $client_id);
        Log::channel('command')->info('onMessage:message:' . $message);
        if (!$messageData) {
            return;
        }

        // 根据类型执行不同的业务
        switch ($messageData['type']) {
            // 客户端回应服务端的心跳
            case 'pong':
                break;
            case 'bindUid':
                $_SESSION['user_id'] = $messageData['user_id'];
                break;
        }
        return;
    }

    /**
     * 当断开连接时
     * @param int $client_id
     */
    public static function onClose($client_id) {
        Log::channel('command')->info('onClose:client_id:' . $client_id);
        $user_id = $_SESSION['user_id'];
        if ($user_id) {
            Gateway::sendToAll(json_encode(array(
                'type' => 'isOnline',
                'time' => date('Y-m-d H:i:s'),
                'data' => ['id' => $user_id, 'is_online' => 0]
            )));
        }
    }

    public static function onWorkerStart($worker) {
        Log::channel('command')->info('onWorkerStart:worker:' . $worker->id);
//        echo 'workman进程启动,进程id ' . $worker->id . PHP_EOL;
    }

    public static function onWebSocketConnect($client_id, $data) {
        Log::channel('command')->info('onWebSocketConnect:client_id:' . $client_id);
        Log::channel('command')->info('onWebSocketConnect:data:' . json_encode($data));
    }

}
