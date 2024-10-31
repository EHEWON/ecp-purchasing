<?php

namespace App\Modules\Admin\Middleware\Project;

use Closure;
use Illuminate\Http\Request;

class ProjectMemberMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }

                if (empty($request['member'])) {
                    check(false, '请选择招标小组');
                }
                $members = $request['member'];
                $memberArr = [];
                $memberIds = [];
                $respBusinessArr = [];
                $directorFlag = false;
                foreach ($members as $member) {
                    if (!isset($member['user_id']) || empty(trim($member['user_id']))) {
                        continue;
                    }
                    if (in_array($member['user_id'], $memberIds)) {
                        check(false, '请不要重复选择供应商');
                    }
                    if (isset($member['is_director']) && $member['is_director'] == '1') {
                        $directorFlag = true;
                    }
                    if (empty($member['resp_business'])) {
                        check(false, '经办业务不能为空');
                    }
                    $respBusinessArr = array_merge($respBusinessArr, $member['resp_business']);
                    $memberIds[] = $member['user_id'];
                    $memberArr[] = $member;
                }
                $respBusinessList = ['A' => '招标立项', 'B' => '供方入围', 'C' => '标书编制', 'F' => '发标', 'H' => '开标', 'I' => '评标', 'K' => '定标'];
                $uniqRespBusinessArr = array_unique($respBusinessArr);

                foreach ($respBusinessList as $key => $respBusinessName) {
                    if (!in_array($key, $uniqRespBusinessArr)) {
                        check(false, '不存在“' . $respBusinessName . '”的经办业务人员');
                    }
                }
                check($directorFlag, '是否负责人最少选择一个');
                check(!empty($memberArr), '请选择招标小组');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }

                if (empty($request['member'])) {
                    check(false, '请选择招标小组');
                }
                $members = $request['member'];
                $memberArr = [];
                $memberIds = [];
                $respBusinessArr = [];
                $directorFlag = false;
                foreach ($members as $member) {
                    if (!isset($member['user_id']) || empty(trim($member['user_id']))) {
                        continue;
                    }
                    if (in_array($member['user_id'], $memberIds)) {
                        check(false, '请不要重复选择招标小组');
                    }
                    if (isset($member['is_director']) && $member['is_director'] == '1') {
                        $directorFlag = true;
                    }
                    if (empty($member['resp_business'])) {
                        check(false, '经办业务不能为空');
                    }
                    $respBusinessArr = array_merge($respBusinessArr, $member['resp_business']);
                    $memberIds[] = $member['user_id'];
                    $memberArr[] = $member;
                }
                $respBusinessList = ['A' => '招标立项', 'B' => '供方入围', 'C' => '标书编制', 'F' => '发标', 'H' => '开标', 'I' => '评标', 'K' => '定标'];
                $uniqRespBusinessArr = array_unique($respBusinessArr);

                foreach ($respBusinessList as $key => $respBusinessName) {
                    if (!in_array($key, $uniqRespBusinessArr)) {
                        check(false, '不存在“' . $respBusinessName . '”的经办业务人员');
                    }
                }
                check($directorFlag, '是否负责人最少选择一个');
                check(!empty($memberArr), '请选择招标小组');
                break;
        }
        return $next($request);
    }

}
