<?php

namespace App\Modules\Admin\Middleware\Project;

use Closure;
use Illuminate\Http\Request;

class ProjectEntryMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                $entrys = !empty($request['entry']) ? $request['entry'] : [];
                foreach ($entrys as $entry) {
                    if (isset($entry['work_load']) && $entry['work_load'] !== '' && $entry['work_load'] < '0') {
                        check(false, '总工期不能为负数');
                    }
                    if (isset($entry['control_amount']) && $entry['control_amount'] !== '' && $entry['control_amount'] < '0') {
                        check(false, '采购控制金额不能为负数');
                    }
                    if (isset($entry['tax_rate']) && $entry['tax_rate'] !== '' && $entry['tax_rate'] < '0') {
                        check(false, '税率须不能为负数');
                    }
                    if (isset($entry['control_vat']) && $entry['control_vat'] !== '' && $entry['control_vat'] < '0') {
                        check(false, '采购控制税额须不能为负数');
                    }
                }
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                foreach ($entrys as $entry) {
                    if (isset($entry['work_load']) && $entry['work_load'] !== '' && $entry['work_load'] <= '0') {
                        check(false, '总工期不能须大于0');
                    }
                    if (isset($entry['control_amount']) && $entry['control_amount'] !== '' && $entry['control_amount'] <= '0') {
                        check(false, '采购控制金额须大于0');
                    }
                }
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty(trim($entry['purentry_content']))) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty(trim($entry['purentry_content'])), '请输入招标内容');
//                    check(!empty(trim($entry['pur_project_id'])), '请选择采购项目');
                }
                check(!empty($entryArr), '请输入物料信息');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                $entrys = !empty($request['entry']) ? $request['entry'] : [];
                foreach ($entrys as $entry) {
                    if (isset($entry['work_load']) && $entry['work_load'] !== '' && $entry['work_load'] < '0') {
                        check(false, '总工期不能为负数');
                    }
                    if (isset($entry['control_amount']) && $entry['control_amount'] !== '' && $entry['control_amount'] < '0') {
                        check(false, '采购控制金额不能为负数');
                    }
                    if (isset($entry['tax_rate']) && $entry['tax_rate'] !== '' && $entry['tax_rate'] < '0') {
                        check(false, '税率须不能为负数');
                    }
                    if (isset($entry['control_vat']) && $entry['control_vat'] !== '' && $entry['control_vat'] < '0') {
                        check(false, '采购控制税额须不能为负数');
                    }
                }
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                foreach ($entrys as $entry) {
                    if (isset($entry['work_load']) && $entry['work_load'] !== '' && $entry['work_load'] <= '0') {
                        check(false, '总工期不能须大于0');
                    }
                    if (isset($entry['control_amount']) && $entry['control_amount'] !== '' && $entry['control_amount'] <= '0') {
                        check(false, '采购控制金额须大于0');
                    }
                }
                $entryArr = [];
                foreach ($entrys as $entry) {
                    if (empty(trim($entry['purentry_content']))) {
                        continue;
                    }
                    $entryArr[] = $entry;
                    check(!empty(trim($entry['purentry_content'])), '请输入招标内容');
//                    check(!empty(trim($entry['pur_project_id'])), '请选择采购项目');
                }
                check(!empty($entryArr), '请输入物料信息');
                break;
        }
        return $next($request);
    }

}
