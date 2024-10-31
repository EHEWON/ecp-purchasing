<?php

namespace App\Modules\Admin\Controller;

class Version {

    public function __construct() {
        
    }

    public function index() {
        $repo = 'EHEWON/ezwork-admin'; // 替换为你的用户名和仓库名
        $api = 'https://api.github.com/repos/' . $repo . '/releases/latest';
        $headers = [
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: ' . date('Y-m-d'),
            'Authorization: Bearer '.''
        ];
        $ch = curl_init($api);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        echo $response;
        if (curl_errno($ch)) {
            return [];
        }
        curl_close($ch);
        return json_decode($response, true);
    }

}
