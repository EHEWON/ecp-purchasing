<?php

namespace App\Common\Util;

use App\Modules\Access\Repository\CommSmsRepo;

class Tool {

    /**
     * 验证短信  验证码
     */
    public function checkCaptcha($mobile, $code) {
        $ret = app()->make(CommSmsRepo::class)->getMobileCaptcha($mobile);

        // 判断验证码时候过期
        self::expCaptcha($ret['create_time']);

        if ($ret['captcha'] != $code) {
            Err('CAPTCHA_ERROR');  //验证码错误
        }
        return $ret;
    }

    //判断验证码是否过期
    public static function expCaptcha($time) {
        $minute = floor((time() - strtotime($time)) % 86400 / 60);
        if ($minute > 5) {
            Err('CAPTCHA_EXP');   //验证码过期
        }
    }

}
