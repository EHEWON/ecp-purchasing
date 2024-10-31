<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProjectMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if (!empty(trim($base['bid_time'])) && $base['bid_time'] <= '0') {
                    check(false, '每轮竞价时长必须大于0');
                }
                if (!empty(trim($base['bid_number'])) && $base['bid_number'] <= '0') {
                    check(false, '参与竞价最少几家必须大于0');
                }
                if (!empty(trim($base['bid_count'])) && $base['bid_count'] <= '0') {
                    check(false, '最多报价次数必须大于0');
                }
                if (!empty(trim($base['max_amount'])) && $base['max_amount'] <= '0') {
                    check(false, '报价最高限额必须大于0');
                }
                if (!empty(trim($base['min_amount'])) && $base['min_amount'] <= '0') {
                    check(false, '报价最低限额必须大于0');
                }
//                if (!empty(trim($base['deposit_flag'])) && $base['deposit_flag'] === 'Y' && !empty(trim($base['cash_deposit'])) && $base['cash_deposit'] <= '0') {
//                    check(false, '竞价保证金必须大于0');
//                }


                if (!empty(trim($base['quotation_trend'])) && trim($base['quotation_trend']) === '2' && !empty(trim($base['reducepct'])) && $base['reducepct'] < '0') {
                    check(false, '每次降价幅度必须大于0');
                }

                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                check(!empty(trim($base['name'])), '请输入项目名称');
                check(!empty(trim($base['person_id'])), '请选择采购员');
                check(!empty(trim($base['enroll_date'])), '请选择报名截止时间');
                check(!empty(trim($base['open_date'])), '请选择预计竞价开始时间');
                check(!empty(trim($base['result_date'])), '请选择预计公布结果时间');
                check(!empty(trim($base['bid_time'])), '请输入每轮竞价时长');
                check(!empty(trim($base['bid_number'])), '请输入参与竞价最少几家');
                check(!empty(trim($base['quotation_trend'])), '请选择报价趋势');
                check(!empty(trim($base['reduce_type'])), '请选择降价方式');
                check(!empty(trim($base['reducepct'])), '请输入每次降价幅度');
                check(!empty(trim($base['bid_count'])), '请输入最多报价次数');
                $time = date('Y-m-d H:i:s');
                check($base['enroll_date'] > $time, '报名截止时间必须大于当前时间');
                check($base['open_date'] > $base['enroll_date'], '预计竞价开始时间必须大于报名截止时间');
                check($base['result_date'] > $base['open_date'], '预计公布结果时间必须大于预计竞价开始时间');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if (!empty(trim($base['bid_time'])) && $base['bid_time'] <= '0') {
                    check(false, '每轮竞价时长必须大于0');
                }
                if (!empty(trim($base['bid_number'])) && $base['bid_number'] <= '0') {
                    check(false, '参与竞价最少几家必须大于0');
                }
                if (!empty(trim($base['bid_count'])) && $base['bid_count'] <= '0') {
                    check(false, '最多报价次数必须大于0');
                }
                if (!empty(trim($base['max_amount'])) && $base['max_amount'] <= '0') {
                    check(false, '报价最高限额必须大于0');
                }
                if (!empty(trim($base['min_amount'])) && $base['min_amount'] <= '0') {
                    check(false, '报价最低限额必须大于0');
                }
//                if (!empty(trim($base['deposit_flag'])) && $base['deposit_flag'] === 'Y' && !empty(trim($base['cash_deposit'])) && $base['cash_deposit'] <= '0') {
//                    check(false, '竞价保证金必须大于0');
//                }


                if (!empty(trim($base['quotation_trend'])) && trim($base['quotation_trend']) === '2' && !empty(trim($base['reducepct'])) && $base['reducepct'] < '0') {
                    check(false, '每次降价幅度必须大于0');
                }
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                check(!empty(trim($base['enroll_date'])), '请选择报名截止时间');
                check(!empty(trim($base['open_date'])), '请选择预计竞价开始时间');
                check(!empty(trim($base['result_date'])), '请选择预计公布结果时间');
                check(!empty(trim($base['bid_time'])), '请输入每轮竞价时长');
                check(!empty(trim($base['bid_number'])), '请输入参与竞价最少几家');
                check(!empty(trim($base['quotation_trend'])), '请选择报价趋势');
                check(!empty(trim($base['reduce_type'])), '请选择降价方式');
                check(!empty(trim($base['reducepct'])), '请输入每次降价幅度');
                check(!empty(trim($base['bid_count'])), '请输入最多报价次数');
                $time = date('Y-m-d H:i:s');
                check($base['enroll_date'] > $time, '报名截止时间必须大于当前时间');
                check($base['open_date'] > $base['enroll_date'], '预计竞价开始时间必须大于报名截止时间');
                check($base['result_date'] > $base['open_date'], '预计公布结果时间必须大于预计竞价开始时间');
                break;
        }
        return $next($request);
    }

}
