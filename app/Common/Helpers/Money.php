<?php

namespace App\Common\Helpers;

class Money {

    //PHP_ROUND_HALF_UP:　　　默认 该模式将进行四舍六入,遇5进1,
    //PHP_ROUND_HALF_DOWN:　　该模式将进行四舍六入,遇5不舍弃
    //PHP_ROUND_HALF_EVEN: 　 该模式将四舍六入,整数部分为奇数则进1
    //PHP_ROUND_HALF_ODD:　　　该模式将四舍六入,整数部分为偶数则进1
    public $strategy = PHP_ROUND_HALF_UP;

    public function calc($num1, $op = '+', $num2) {
        switch ($op) {
            case '+' :
                return $this->add($num1, $num2);
                break;
            case '-' :
                return $this->sub($num1, $num2);
                break;
            case '*' :
                return $this->mul($num1, $num2);
                break;
            case '/' :
                return $this->div($num1, $num2);
                break;
            case '==' :
                return $this->comp($num1, $num2);
                break;
            default :
                break;
        }
    }

    //加
    public function add($num1, $num2) {
        return $this->format(bcadd($num1, $num2, 4));
    }

    //减
    public function sub($num1, $num2) {
        return $this->format(bcsub($num1, $num2, 4));
    }

    //乘
    public function mul($num1, $num2) {
        return $this->format(bcmul($num1, $num2, 4));
    }

    //除
    public function div($num1, $num2) {
        return $this->format(bcdiv($num1, $num2, 4), 4);
    }

    //比较
    public function comp($num1, $num2) {
        if (bccomp($num1, $num2) == 0)
            return true;
        else
            return false;
    }

    //格式化
    public function format($num, $f = 2) {
        $ret = round($num, $f, $this->strategy);
        return sprintf("%." . $f . "f", $ret);
    }

    //计算手续费,万分之
    public function getWanRate($amount, $rate) {
        $rate = $this->div($rate, 100);
        return $this->getRate($amount, $rate);
    }

    //百分之手续费计算
    public function getRate($amount, $rate) {

        $rate = $this->div($rate, 100);
        $value = $this->mul($amount, $rate);
        //log::info($amount.'.......getRate.getRate.......'.$value);
        return $this->format($value);
    }

    /**
     * 费率计算
     * 1 如果amount>0 则比较 amount和max 返回最小值
     * 2 否则 返回max值
     */
    public function getAmount($amount = null, $maxAmt = null) {
        $result = 0;
        if ($amount > 0) {
            if ($maxAmt > 0) {
                $result = $amount > $maxAmt ? $maxAmt : $amount;
            } else {
                $result = $amount;
            }
        } else {
            $result = $maxAmt;
        }
        return $this->format($result);
    }

    /**
     * 获取 用户|商户到账金额
     * @desc 根据业务类型，资费编码和交易金额计算到账金额
     */
    public function getReceiveAmt($tariffRate, $transAmount) {
        //log::info("getReceiveAmt>>>>...................................");
        $result = [];
        //手续费
        //log::info($transAmount.'..................'.$tariffRate['rate']);
        $rateAmt = $this->getWanRate($transAmount, $tariffRate['rate']);
        //log::info('rateAmt...................'.$rateAmt);
        //手续费封顶(固定值)
        $maxAmt = $tariffRate['max_rate'];
        //附加手续费()
        $baseAmt = $this->getWanRate($transAmount, $tariffRate['base_rate']);
        //封顶(固定值)
        $baseMaxAmt = $tariffRate['base_max_rate'];
        //总费用
        $fee1 = $this->getAmount($rateAmt, $maxAmt);
        $fee2 = $this->getAmount($baseAmt, $baseMaxAmt);
        $fee = $this->calc($fee1, '+', $fee2);

        $result['receiveAmt'] = $this->calc($transAmount, '-', $fee); //到账金额
        $result['fee'] = $fee; //手续费
        return $result;
    }

    /**
     * 获取 通道成本
     */
    public function getChannelCost($rate, $transAmount) {
        //成本
        $cost = $this->getWanRate($transAmount, $rate['cost_rate']);
        //成本封顶
        $costMax = $rate['cost_max_rate'];
        //附加费
        $norm = $this->getWanRate($transAmount, $rate['norm_rate']);
        //附加费封顶
        $normMax = $rate['norm_max_rate'];
        //清算费率
        $adv = $this->getWanRate($transAmount, $rate['advance_rate']);
        //清算附加费
        $advMax = $rate['advance_max_rate'];
        //总费用
        $fee1 = $this->getAmount($cost, $costMax);
        $fee2 = $this->getAmount($norm, $normMax);
        $fee3 = $this->getAmount($adv, $advMax);
        $fee12 = $this->calc($fee1, '+', $fee2);
        $fee = $this->calc($fee12, '+', $fee3);
        //格式化
        $result = $this->format($fee);
        return $result;
    }

    /**
     * 元转化为分
     */
    public function getYuan2Fen($yuan) {
        $fen = $this->mul($yuan, 100);
        $value = round($fen, 0, $this->strategy);
        $result = sprintf('%.0f', $value);
        return $result;
    }

    public function getFen2Yuan($fen) {
        $yuan = $this->div($fen, 100);
        return $this->format($yuan);
    }

    //万分比转化为百分比
    public function getWan2Percent($rate) {
        $ret = $this->div($rate, 100);
        return $this->format($ret);
    }

}
