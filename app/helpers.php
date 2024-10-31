<?php

use App\Jobs\GatewayJob;
use App\Modules\Admin\Repository\UserPurchaserRepo;
use Illuminate\Contracts\Bus\Dispatcher;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\{
    Facades\DB,
    Facades\Lang,
    Facades\Redis,
    Str
};

if (!function_exists('Err')) {

    function Err($message, $code = 9999) {

        if (Str::contains($message, ":")) {
            list($message, $code) = explode(':', $message);
        } else {
            $code = config('const_response.' . $message . '.code', $code);
            $message = config('const_response.' . $message . '.msg', $message);
        }
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message);
        }

        if (DB::transactionLevel()) {
            DB::rollback();
        }
        check(false, $message, $code);
//        throw new Exception($message, $code);
    }

}
if (!function_exists('chatIdentify')) {

// 拼接聊天对象
    function chatIdentify($fromUser, $toUser) {
        $identify = [$fromUser, $toUser];
        sort($identify);
        return implode('-', $identify);
    }

}
if (!function_exists('Guid')) {

    function Guid() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

}
if (!function_exists('filterMenus')) {

    function filterMenus($data, $pid = 0) {
        $tree = [];
        foreach ($data as $k => $v) {
            if ($v['parent_id'] == $pid) {
                $child = filterMenus($data, $v['id']);
                if (!empty($child)) {
                    $v['children'] = $child;
                } else {
                    $v['children'] = '';
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

}

if (!function_exists('previewUrl')) {

// 预览文件
    function previewUrl($url) {
        $previewConf = config('preview');
        $preview = '';
        $suffix = explode('.', $url);
        $ext = $suffix[count($suffix) - 1];
        $media = ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'pdf', 'mp3', 'wav', 'wmv', 'amr', 'mp4', '3gp', 'avi', 'm2v', 'mkv', 'mov', 'webp'];
        $doc = ['ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
        if (in_array($ext, $media) && $previewConf['own']) {
            $preview = $previewConf['own'] . "view.html?src=" . $url;
        } elseif (in_array($ext, $doc) && $previewConf['yzdcs']) {
            $preview = $previewConf['yzdcs'] . '?k=' . $previewConf['keycode'] . '&url=' . $url;
        } else {
            $preview = request()->domain() . "view.html?src=" . $url;
        }
        return $preview;
    }

}
if (!function_exists('ID')) {

    function ID() {
        return app()->make(\App\Common\Helpers\IdWorker::class)->getId();
    }

}


if (!function_exists('Token')) {

    function Token() {
        return app()->make(\App\Common\Jwt\Token::class);
    }

}

if (!function_exists('Money')) {

    function Money() {
        return app()->make(\App\Common\Helpers\Money::class);
    }

}

if (!function_exists('Repo')) {

    function Repo($repository) {
        return app()->make($repository);
    }

}

if (!function_exists('DICode')) {

    function DICode($confile, $param) {
        $confile = 'const_' . $confile;
        $code = \Illuminate\Support\Facades\Config::get($ityonfile . '.' . $param . '.code');
        $code = $code != null ? $code : '9999';
        return $code;
    }

}

if (!function_exists('DIMsg')) {

    function DIMsg($confile, $param) {
        $confile = 'const_' . $confile;
        $msg = \Illuminate\Support\Facades\Config::get($confile . '.' . $param . '.msg');
        $msg = $msg ? $msg : '未知错误';
        return $msg;
    }

}


/** 名字*处理 */
if (!function_exists('C')) {

    function C($str) {
        $length = mb_strlen($str, 'UTF8');
        if ($length <= 0)
            return '*';

        $first = mb_substr($str, 0, 1, 'utf-8') . '*';
        $last = '';
        if ($length >= 3) {
            $last = mb_substr($str, -1, 1, 'utf-8');
        }

        return $first . $last;
    }

}

/* 手机号 * 处理 */
if (!function_exists('Mobile')) {

    function Mobile($value) {
        $prefix = substr($value, 0, 3);
//截取身份证号后4位
        $suffix = substr($value, -4, 4);

        return $prefix . "****" . $suffix;
    }

}
/**
 * 获取客户端IP
 */
if (!function_exists('get_client_info')) {

    /**
     * 获取IP与浏览器信息、语言
     *
     * @return array
     */
    function get_client_info(): array {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $XFF = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $client_pos = strpos($XFF, ', ');
            $client_ip = false !== $client_pos ? substr($XFF, 0, $client_pos) : $XFF;
            unset($XFF, $client_pos);
        } else
            $client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? '0.0.0.0';
        $client_lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5) : '';
        $client_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return [
            'ip' => &$client_ip,
            'lang' => &$client_lang,
            'agent' => &$client_agent,
        ];
    }

}

if (!function_exists('get_ip')) {

    function get_ip(): string {
        $data = get_client_info();
        return $data['ip'] ?? '';
    }

}
/**
 * 分享链结URL设定
 */
if (!function_exists('Share')) {

    function Share($kind_of) {
        switch ($kind_of) {
//            case '01' : return config('const_share.URL.goods');
            case '01' : return config('const_share.URL.app');
            case '02' : return config('const_share.URL.goods');
            case '03' : return config('const_share.URL.cafe');
            case '04' : return config('const_share.URL.top');
            case '05' : return config('const_share.URL.sign_up');
        }
    }

}



/**
 * @desc 获取数据字典
 */
if (!function_exists('getConfigure')) {

    function getConfigure($code, $key) {
        $ret = app(\App\Modules\Misc\Repository\CommCodeMasterRepo::class)
                ->getConfigure($code, $key);
        $ret['code'] = $code;
        $ret['key'] = $key;
        return $ret;
    }

}

if (!function_exists('check')) {

    function check(bool $assert, $message, $code = 1, $ret = 1) {
        if (!$assert) {
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(['ret' => $ret,
                'data' => [],
                'response_at' => date('Y-m-d H:i:s'),
                'code' => $code,
                'message' => $message], JSON_UNESCAPED_UNICODE);
            die;
        }
    }

}

if (!function_exists('randomnum')) {

    function randomnum($len = 4) {
        $str = "";
        $str_pol = "0123456789";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < $len; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}
if (!function_exists('isEmail')) {
    /*
     * 判断邮箱
     *
     * @param int $size
     * @return string
     * @author jhw
     */

    function isEmail($email) {
        $mode = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        if (preg_match($mode, $email)) {
            return true;
        } else {
            return false;
        }
    }

}
if (!function_exists('checkDateFormat')) {

    function checkDateFormat($date) {
//匹配日期格式
        $parts = [];
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
//检测是否为日期
            if (checkdate($parts[2], $parts[3], $parts[1])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
if (!function_exists('checkDateTimeFormat')) {

    function checkDateTimeFormat($date) {
//匹配日期格式
        $parts = [];
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-5][0-9]):([0-5][0-9]):([0-5][0-9])$/", $date, $parts)) {
//检测是否为日期
            if (checkdate($parts[2], $parts[3], $parts[1])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

if (!function_exists('checkEmail')) {

    function checkEmail($email, $message) {
        check($email, $message);
    }

}


if (!function_exists('list_to_tree')) {

    function list_to_tree($list, $primary_key = 'menu_id', $pid = 'parent_id', $child = '_child', $root = 0): array {
        $tree = [];
        if (is_array($list)) {
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$primary_key]] = & $list[$key];
            }
            foreach ($list as $key => $data) {
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = & $list[$key];
                } elseif (isset($refer[$parentId])) {
                    $parent = & $refer[$parentId];
                    $parent[$child][] = & $list[$key];
                }
            }
        }
        return $tree;
    }

}

if (!function_exists('randomstr')) {

    function randomstr($len = 6) {
        $str = "";
        $str_pol = "0123456789qwertyuiopasdfghjklzxcvbnm";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < $len; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}

if (!function_exists('display_timespan')) {

    function display_timespan($datetime) {
        if (empty($datetime))
            return '';
        $diff = time() - strtotime($datetime);
        $str = '';
        if ($diff <= 2) {
            $str = '刚刚';
        } elseif ($diff < 60) {
            $str = sprintf('%d秒前', $diff);
        } elseif ($diff < 3600) {
            $str = sprintf('%d分钟前', floor($diff / 60));
        } elseif ($diff < 86400) {
            $str = sprintf('%d小时前', floor($diff / 3600));
        } elseif ($diff < 2592000) { // 30天
            $str = sprintf('%d天前', floor($diff / 86400));
        } else if ($diff < 31104000) { // 一年
            $str = sprintf('%d月前', floor($diff / 86400 / 30));
        } else {
            $str = sprintf('%d年前', floor($diff / 86400 / 30 / 12));
        }
        return $str;
    }

}

if (!function_exists('timeIsOlderThan')) {

    function timeIsOlderThan($checkAt) {
        $checkTime = strtotime($checkAt);
        $time = time();
        $leftTime = $time - $checkTime;
        if ($leftTime <= 60) {
            return 'Just Now';
        }
        $str = '';
        $leftYears = '';
        $leftMonths = '';
        if ($leftTime / 31536000 >= 1) {
            $leftYears = intval($leftTime / 31536000);
            $str .= $leftYears . ' years ';
        }
        $leftMonth = $leftTime % 31536000;
        if ($leftMonth / 2592000 >= 1) {
            $leftMonths = intval($leftMonth / 2592000);
            $str .= $leftMonths . ' months ';
        }
        if (!empty($leftYears)) {
            return $str;
        }

        $leftWeek = $leftMonth % 2592000;
        if ($leftWeek / 604800 >= 1 && empty($leftYears)) {
            $leftWeeks = intval($leftWeek / 604800);
            $str .= $leftWeeks . ' weeks ';
        }
        if (!empty($leftMonths)) {
            return $str;
        }

        $leftDay = $leftWeek % 604800;
        if ($leftDay / 86400 >= 1 && empty($leftYears)) {
            $leftDays = intval($leftDay / 86400);
            $str .= $leftDays . ' days ';
        }
        if (!empty($leftWeeks) || !empty($leftDays)) {
            return $str;
        }
        $leftHour = $leftDay % 86400;
        if ($leftHour / 3600 >= 1 && empty($leftDays) && empty($leftMonths) && empty($leftYears)) {
            $leftHours = intval($leftHour / 3600);
            return $leftHours . '  Hours ago ';
        }
        $leftMinit = $leftDay % 3600;
        if ($leftMinit / 60 >= 1 && empty($leftDays) && empty($leftMonths) && empty($leftYears)) {
            $leftHours = intval($leftHour / 60);
            return $leftHours . ' Minutes ago ';
        }
        return $str;
    }

}

if (!function_exists('leftTimeDisplay')) {

    function leftTimeDisplay($future_time) {
        $futureTime = strtotime($future_time);
        $time = time();
        $leftTime = $futureTime - $time;
        if ($leftTime < 0) {
            return '';
        }
        if ($leftTime <= 60) {
            return Lang::get('common.less_minute');
        }
        $str = '';
        $leftYears = '';
        $leftMonths = '';
        if ($leftTime / 31536000 >= 1) {
            $leftYears = intval($leftTime / 31536000);
            $str .= $leftYears . Lang::get('common.years');
        }
        $leftMonth = $leftTime % 31536000;
        if ($leftMonth / 2592000 >= 1) {
            $leftMonths = intval($leftMonth / 2592000);
            $str .= $leftMonths . Lang::get('common.months');
        }
        if (!empty($leftYears)) {
            return $str;
        }

        $leftWeek = $leftMonth % 2592000;
        if ($leftWeek / 604800 >= 1 && empty($leftYears)) {
            $leftWeeks = intval($leftWeek / 604800);
            $str .= $leftWeeks . Lang::get('common.weeks');
        }
        if (!empty($leftMonths)) {
            return $str;
        }

        $leftDay = $leftWeek % 604800;
        if ($leftDay / 86400 >= 1 && empty($leftYears)) {
            $leftDays = intval($leftDay / 86400);
            $str .= $leftDays . Lang::get('common.days');
        }
        if (!empty($leftWeeks) || !empty($leftDays)) {
            return $str;
        }
        $leftHour = $leftDay % 86400;
        if ($leftHour / 3600 >= 1 && empty($leftDays) && empty($leftMonths) && empty($leftYears)) {
            $leftHours = intval($leftHour / 3600);
            return $leftHours . Lang::get('common.hours');
        }
        $leftMinit = $leftDay % 3600;
        if ($leftMinit / 60 >= 1 && empty($leftDays) && empty($leftMonths) && empty($leftYears)) {
            $leftHours = intval($leftHour / 60);
            return $leftHours . Lang::get('common.minutes');
        }
        return $str;
    }

}

if (!function_exists('explodeCharacters')) {

    function explodeCharacters($urlstr, $is_limit_length = true) {
        $match = [];
        if (!is_string($urlstr)) {
            return'';
        }
        $str = urldecode($urlstr);
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $str) > 0) {
            $pinyin = new Overtrue\Pinyin\Pinyin();
            $purl = $pinyin->permalink($str);
            $urlArr = explode('-', $purl);
            $urlChunk = array_chunk($urlArr, 15, true);
            return !empty($urlChunk[0]) ? implode('-', $urlChunk[0]) : '';
        }
        ruLetterReplaceEnLetter($str);
        preg_match_all('/([a-zA-Z0-9àí]+)/u', urldecode($str), $match);
        $newstr = '';
        $i = 0;
        foreach ($match[1] as $item) {
            if (!empty($item) && ($is_limit_length === true && $i < 10)) {
                $newstr .= '-' . $item;
                $i++;
            }
        }
        return ltrim($newstr, '-');
    }

}
if (!function_exists('ruLetterReplaceEnLetter')) {

    /**
     * 俄语单词转换成拉丁字母
     */
    function ruLetterReplaceEnLetter(&$str) {

        $pattern = ['/а/', '/б/', '/в/', '/г/', '/д/', '/е/', '/ё/', '/ж/', '/з/', '/и/', '/й/', '/к/', '/л/', '/м/', '/н/', '/о/', '/п/', '/р/', '/с/', '/т/', '/у/', '/ф/', '/х/', '/ц/', '/ч/', '/щ/', '/ш/', '/ь/', '/ъ/', '/э/', '/ю/', '/я/', '/ы/', '/A/', '/Б/', '/В/', '/Г/', '/Д/', '/Е/', '/Ё/', '/Ж/', '/З/', '/И/', '/Й/', '/К/', '/Л/', '/М/', '/Н/', '/О/', '/П/', '/Р/', '/С/', '/Т/', '/У/', '/Ф/', '/Х/', '/Ц/', '/Ч/', '/Щ/', '/Ш/', '/Ь/', '/Ъ/', '/Э/', '/Ю/', '/Я/', '/Ы/',];
        $replacement = ['a', 'b', 'v', 'g', 'd', 'e', 'je', 'zh', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 'c', 't', 'u', 'f', 'h', 'c', 'ch', 'sc', 'sh', '', '', 'e', 'ju', 'ia', 'i', 'A', 'B', 'V', 'G', 'D', 'E', 'JE', 'ZH', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'C', 'T', 'U', 'F', 'H', 'C', 'CH', 'SC', 'SH', '', '', 'E', 'JU', 'IA', 'I',];
        $str = preg_replace($pattern, $replacement, urldecode($str));
    }

}

if (!function_exists('wsSendMsg')) {

//gateway向web页面推送消息
    function wsSendMsg($user, $type, $data, $isGroup = 0) {
        $message = [
            'type' => $type,
            'user' => $user,
            'time' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        app(Dispatcher::class)->dispatch(new GatewayJob($message, 'wsSendMsg'));
    }

}
if (!function_exists('bindGroup')) {

    /**
     * 将用户团队绑定到消息推送服务中
     * @return \think\response\Json
     */
    function bindGroup($input) {
        app(Dispatcher::class)->dispatch(new GatewayJob($input, 'bindGroup'));
        return true;
    }

}
if (!function_exists('bindUid')) {

    /**
     * 将用户UId绑定到消息推送服务中
     * @return \think\response\Json
     */
    function bindUid($input) {
        app(Dispatcher::class)->dispatch(new GatewayJob($input, 'bindUid'));
        return true;
    }

}
if (!function_exists('doBindUid')) {

// 执行绑定
    function doBindUid($userId, $clientId, $bidBillId) {
        $input = [
            'user_id' => $userId,
            'client_id' => $clientId,
            'bid_bill_id' => $bidBillId
        ];
        app(Dispatcher::class)->dispatch(new GatewayJob($input, 'doBindUid'));
    }

}
if (!function_exists('offline')) {

// 下线通知
    function offline($input) {
        app(Dispatcher::class)->dispatch(new GatewayJob($input, 'offline'));
    }

}
if (!function_exists('isMobile')) {

    function isMobile() {
// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return TRUE;
        }
// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE; // 找不到为flase,否则为TRUE
        }
// 判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'mobile',
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipad',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'huawei',
                'Coolpad',
                'EVA',
                'ZTE',
                'OPPO',
                'Redmi',
                'vivo',
            );
// 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return TRUE;
            }
        }
        if (isset($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
// 如果只支持wml并且不支持html那一定是移动设备
// 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return TRUE;
            }
        }
        return FALSE;
    }

}

if (!function_exists('formatFilesize')) {

    /**
     * 将字节数转换为更可读的格式
     * @param int $filesize 文件大小，单位是字节
     * @param int $precision 可选参数，规定小数点后保留几位。默认为2位。
     * @return string 可读的文件大小，如1.20kB、3.45MB、2.00GB等。
     * */
    function formatFilesize(int $filesize, int $precision = 2): string {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $i = 0;
        while ($filesize >= 1024 && $i < 4) {
            $filesize /= 1024;
            $i++;
        }
        $filesize = round($filesize, $precision);
        return $filesize . $units[$i];
    }

}

if (!function_exists('lnformat')) {

    function lnformat($number) {
        try {
            $num = number_format($number, 2, '.', ',');
            $numArr = explode('.', $num);
            if (isset($numArr[1]) && $numArr[1] === '00') {
                return $numArr[0];
            } elseif (isset($numArr[1]) && $numArr[1][1] === '0') {
                return $numArr[0] . '.' . $numArr[1][0];
            }
            return $num;
        } catch (Exception $ex) {
            return $number;
        }
    }

}


if (!function_exists('checkAgent')) {

    function checkAgent() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'micromessenger')) {
            if (strpos($agent, 'miniprogram')) {
                return 'wechat_mp';
            } else {
                return 'wechat_h5';
            }
        }
        return 'pc';
    }

}
if (!function_exists('mergeSpaces')) {

    function mergeSpaces($string) {
        return preg_replace("/\s(?=\s)/", "\\1", trim($string));
    }

}
if (!function_exists('trims')) {

    function trims($object) {
        if (is_string($object)) {
            return trim($object);
        } elseif (is_array($object)) {
            foreach ($object as &$val) {
                trims($val);
            }
            return $object;
        } elseif (is_object($object)) {
            $arr = json_decode(json_encode($object), true);
            foreach ($arr as &$val) {
                trims($val);
            }
            return $arr;
        }
    }

}
if (!function_exists('is_mobile')) {

    function is_mobile($mobile) {
        $pattern = '/^1[3456789]\d{9}$/'; // 这里的正则表达式只能验证国内的手机号码
        if (preg_match($pattern, $mobile)) {
            return true;
        } else {
            return false;
        }
    }

}
if (!function_exists('check_userPerms')) {

    function check_userPerms() {
        $token1 = Auth::guard('admin')->getToken();
//var_dump('cur_pid'.$token1);die;
        $admin = Auth::guard('admin')->user();
        if (empty($admin)) {
            $data['ret'] = false;
            $data['message'] = '管理员数据无法查询';
            return $data;
        }

        $cur_pid = Redis::get('cur_pid_' . $token1);
        $data['user_type'] = $admin['user_type'];
        $data['cur_pid'] = $cur_pid;
        $data['user_id'] = $admin['user_id'];

        $roles = json_decode(Redis::get('role_' . $admin->user_type . '_' . $cur_pid . '_' . $token1));
        if ($cur_pid == 1 && in_array(1, $roles)) {
            $data['ret'] = true;
            $data['data_type'] = 'admin';
            return $data;
        }
        $perms = json_decode(Redis::get('perm_' . $admin->user_type . '_' . $cur_pid . '_' . $token1));
        if (empty($perms)) {
            $data['ret'] = false;
            $data['message'] = 'redis存储token过期';
            return $data;
        }
//var_dump($perms);die;
        $sope = ['all', 'department', 'user'];
        $routeInfo = app('request')->route();
//$routes = app()->router->getRoutes();
//       var_dump(app('request')->path());die;
        if (!empty($routeInfo[0])) {
            $route_url = $routeInfo[1]['uses'];
            if (($pos = strpos($route_url, "@")) !== false) {
                $action = strtolower(substr($route_url, $pos + 1));
                $controller = explode("Controller", current(explode("@", $route_url)));
                $controller = isset($controller[1]) ? str_replace('\\', '', strtolower($controller[1])) : '';
            } else {
                $action = "";
                $controller = "";
            }
        } else {
            $action = "";
            $controller = "";
        }
        if (!empty($perms) && !empty($controller)) {
            $url = $controller . '/' . $action;
        } else {
            $data['ret'] = false;
            $data['message'] = '无权使用' . $url;
            return $data;
        }
        $data['orgIds'] = [];
        foreach ($sope as $k) {
            $url_perms = $url . ':' . $k;
            if (in_array($url_perms, $perms) || in_array('/' . $url_perms, $perms)) {
                $data['ret'] = true;
                $data['data_type'] = $k;
                if ($data['data_type'] == 'department') {
                    $userPurchaser = new UserPurchaserRepo();
                    $OrgIds = $userPurchaser->getUserOrgIds($admin['user_id'], $cur_pid)->toArray();
                    $data['orgIds'] = $OrgIds;
                }
                if ($data['data_type'] == 'all' && $cur_pid == 1) {
                    $data['data_type'] = 'admin';
                }
                return $data;
            }
        }

        $data['ret'] = false;
        $data['message'] = '无权使用' . $url;
        return $data;
    }

}

if (!function_exists('dataTrim')) {

    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-02-02
     */
    function dataTrim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = dataTrim($v);
            }
            return $data;
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = dataTrim($v);
            }
            return $data;
        }
        if (is_string($data)) {
            return trim($data);
        }
        return $data;
    }

}
if (!function_exists('RecursiveMkdir')) {

    function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

}
if (!function_exists('getTaxCalTypeText')) {

    function getTaxCalTypeText($taxCalType) {
        switch (strtoupper($taxCalType)) {
            case '1':
                return '价外税(含税)';
            case '2':
                return '价外税(不含税)';
            case '3':
                return '价内税(含税)';
        }
    }

}

if (!function_exists('APISTORE')) {

    function APISTORE($url, $param = null, $ispost = false) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $ispost ? $url : $url . '?' . http_build_query($param));
//如果是https协议
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
//CURL_SSLVERSION_TLSv1
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
//USERAGENT
        curl_setopt($curl, CURLOPT_USERAGENT, 'APIStore');
//超时时间
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
//通过POST方式提交
        if ($ispost) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//返回内容
        $callbcak = curl_exec($curl);
//关闭,释放资源
        curl_close($curl);
//返回内容JSON_DECODE
        return json_decode($callbcak, true);
    }

}
if (!function_exists('enterpriseType')) {

    function enterpriseType($regType) {
        if (strpos('有限责任公司', $regType) !== false) {
            return 'ENTERPRISE';
        } elseif (strpos('机关', $regType) !== false) {
            return 'STATE_ORGANS';
        } elseif (strpos('事业单位', $regType) !== false) {
            return 'PUBLIC_INSTITUTIONS';
        } elseif (strpos('团体', $regType) !== false) {
            return 'SOCIAL_GROUPS';
        } elseif (strpos('个体', $regType) !== false) {
            return 'INDIVIDUAL_BUSINESSES';
        } elseif (strpos('自然人', $regType) !== false) {
            return 'NATURAL_PERSON';
        } else {
            return '';
        }
    }

}
if (!function_exists('download2local')) {

    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    function download2local($tmpSavePath, $remoteFile, $attach_name) {
//设置本地临时保存目录
        $localFullFileName = $tmpSavePath . mb_convert_encoding(urldecode(basename($attach_name)), 'GB2312', 'UTF-8');
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $file = fopen($remoteFile, 'rb', null, $context);
        if ($file) {
            $newf = fopen($localFullFileName, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $localFullFileName;
    }

}
if (!function_exists('ready2import')) {


    function ready2import($localFile, $pIndex = 0) {
//获取文件类型
        $fileType = IOFactory::identify($localFile);
//创建PHPExcel读取对象
        $objReader = IOFactory::createReader($fileType);
//加载文件并读取
        $officeSheet = $objReader->load($localFile);
        $data = $officeSheet->getSheet($pIndex)->toArray();
        return $data;
    }

}
if (!function_exists('ready2import')) {

    function redirect($url) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        exit;
    }

}
if (!function_exists('uri')) {

    /**
     * Url生成
     * @param string        $url 路由地址
     * @param string|array  $vars 变量
     * @return string
     * 老url
     */
    function uri($url = '', $vars = '') {
        $page = isset($vars['page']) ? $vars['page'] : 1;
        $pageSize = isset($vars['pagesize']) ? $vars['pagesize'] : 10;
        if ($pageSize !== '[PAGESIZE]') {
            $pagesize = $pageSize <= 10 ? 10 : ($pageSize >= 20 & $pageSize < 50 ? 20 : ($pageSize >= 50 ? 50 : 10));
        } else {
            $pagesize = '[PAGESIZE]';
        }
        if ($page == 1 && $pagesize == 10 && empty($vars)) {
            return '/frontend/list';
        } else {
            return '/frontend/list?' . http_build_query($vars);
        }
    }

}
