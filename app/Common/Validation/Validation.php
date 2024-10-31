<?php

namespace App\Common\Validation;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator;

class Validation extends Validator {

    /**
     * 身份证格式验证
     */
    public function ValidateIdentitycards($translator, $data, $rules, $messages) {
        $vCity = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $data))
            return false;
        if (!in_array(substr($data, 0, 2), $vCity))
            return false;
        $data = preg_replace('/[xX]$/i', 'x', $data);
        $vLength = strlen($data);
        if ($vLength == 18) {
            $vBirthday = substr($data, 6, 4) . '-' . substr($data, 10, 2) . '-' . substr($data, 12, 2);
        } else {
            $vBirthday = '19' . substr($data, 6, 2) . '-' . substr($data, 8, 2) . '-' . substr($data, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday)
            return false;
        if ($vLength == 18) {
            $vSum = 0;
            for ($i = 17; $i >= 0; $i--) {
                $vSubStr = substr($data, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'x') ? 10 : intval($vSubStr, 11));
            }
            if ($vSum % 11 != 1)
                return false;
        }
        return $data;
    }

    /**
     * 验证手机号
     */
    public function ValidateMobile($translator, $data, $rules, $messages) {
        if ($data) {
            return preg_match('/^1[345789]\d{1}\d{8}$/', $data);
        }
        return '';
    }

    /**
     * 银行卡验证
     * 16-19 位卡号校验位采用 Luhm 校验方法计算：
     * 1，将未带校验位的 15 位卡号从右依次编号 1 到 15，位于奇数位号上的数字乘以 2
     * 2，将奇位乘积的个十位全部相加，再加上所有偶数位上的数字
     * 3，将加法和加上校验位能被 10 整除。
     */
    public function ValidateAccountNo($translator, $data, $rules, $messages) {

        $arr_no = str_split($data);
        $last_n = $arr_no[count($arr_no) - 1];


        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        if (($total % 10) != 0) {
            return false;
        }

        return $data;
    }

    public function ValidateDefault($translator, $data, $rules, $messages) {
        return $data;
    }

    public function ValidateType($translator, $data, $rules, $messages) {
        return $data;
    }

    public function ValidateDesc($translator, $data, $rules, $messages) {
        return $data;
    }

}
