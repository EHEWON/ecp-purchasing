<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\UserLogin;

class UserLoginRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new UserLogin();
        parent::__construct($this->model);
    }

    public function addLog($userId) {
        $ip = get_ip();

        $this->model->insert([
            'user_id' => $userId,
            'ip' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getAddress($ip) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.bejson.com/Bejson/Api/Ip/getIp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'ip=' . $ip);
        if (curl_errno($ch)) {
            $data = '';
        } else {
            $data = curl_exec($ch);
        }
        curl_close($ch);
        return json_decode($data, true);
    }

}
