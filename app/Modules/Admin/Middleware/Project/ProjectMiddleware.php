<?php

namespace App\Modules\Admin\Middleware\Project;

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
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                check(!empty($base['name']) && !empty(trim($base['name'])), '请输入项目名称');
                check(!empty($base['bid_mode_id']) && !empty(trim($base['bid_mode_id'])), '请选择采购方式');
                check(!empty($base['invitation_deadline']) && !empty(trim($base['invitation_deadline'])), '请选择确认截止时间');
                check(!empty($base['doc_type']) && !empty(trim($base['doc_type'])), '请选择招标范围');
                check(!empty($base['qualification_required']) && !empty(trim($base['qualification_required'])), '请输入资质要求');
                check(!empty($base['bid_open_type']) && !empty(trim($base['bid_open_type'])), '请选择开标方式');
                check(!empty($base['evaluate_decide_way_id']) && !empty(trim($base['evaluate_decide_way_id'])), '请选择评定标方法');
                check(!empty($base['evaluated_method']) && !empty(trim($base['evaluated_method'])), '请选择评标方式');
                if ($base['evaluate_decide_way_id'] == '1') {
                    check(!empty($base['bid_eval_template']) && !empty(trim($base['bid_eval_template'])), '请选择评标模板');
                }
                $time = date('Y-m-d H:i:s');
                if (!empty($base['invitation_deadline'])) {
                    $base['invitation_deadline'] = date('Y-m-d H:i:s', strtotime($base['invitation_deadline']));
                }
                if (!empty($base['supplier_invi_end_date'])) {
                    $base['supplier_invi_end_date'] = date('Y-m-d H:i:s', strtotime($base['supplier_invi_end_date']));
                }
                if (!empty($base['commercial_doc_end_date'])) {
                    $base['commercial_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['commercial_doc_end_date']));
                }
                if (!empty($base['technical_doc_end_date'])) {
                    $base['technical_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['technical_doc_end_date']));
                }
                if (!empty($base['bid_open_deadline'])) {
                    $base['bid_open_deadline'] = date('Y-m-d H:i:s', strtotime($base['bid_open_deadline']));
                }
                if (!empty($base['bid_publish_date'])) {
                    $base['bid_publish_date'] = date('Y-m-d', strtotime($base['bid_publish_date']));
                }
                if (!empty($base['bid_evaluation_date'])) {
                    $base['bid_evaluation_date'] = date('Y-m-d', strtotime($base['bid_evaluation_date']));
                }
                if (!empty($base['bid_decision_date'])) {
                    $base['bid_decision_date'] = date('Y-m-d', strtotime($base['bid_decision_date']));
                }
                if (!empty($base['approach_date'])) {
                    $base['approach_date'] = date('Y-m-d', strtotime($base['approach_date']));
                }
                check($base['invitation_deadline'] > $time, '确认截止时间须晚于当前时间');
                check($base['supplier_invi_end_date'] > $base['invitation_deadline'], '入围完成时间须晚于确认截止时间');

                if ($base['evaluate_decide_way_id'] == '1') {
                    check($base['commercial_doc_end_date'] > $base['invitation_deadline'], '商务标编制完成时间须晚于确认截止时间');
                }
                if ($base['evaluate_decide_way_id'] == '1' && $base['doc_type'] == '1') {
                    check($base['technical_doc_end_date'] > $base['invitation_deadline'], '技术标编制完成时间须晚于确认截止时间');
                }
                check($base['bid_publish_date'] . ' 23:59:59' > $base['supplier_invi_end_date'], '发标日期须晚于入围完成时间');
                if ($base['evaluate_decide_way_id'] == '1') {
                    check($base['bid_publish_date'] . ' 23:59:59' > $base['commercial_doc_end_date'], '发标日期须晚于商务标编制完成时间');
                }
                if ($base['evaluate_decide_way_id'] == '1' && $base['doc_type'] == '1') {
                    check($base['bid_publish_date'] . ' 23:59:59' > $base['technical_doc_end_date'], '发标日期须晚于技术标编制完成时间');
                }
                check($base['bid_open_deadline'] > $base['bid_publish_date'], '截标开标时间须晚于发标日期');
                if ($base['evaluate_decide_way_id'] == '1') {
                    check($base['bid_open_deadline'] > $base['commercial_doc_end_date'], '截标开标时间须晚于商务标编制完成时间');
                }
                if ($base['evaluate_decide_way_id'] == '1' && $base['doc_type'] == '1') {
                    check($base['bid_open_deadline'] > $base['technical_doc_end_date'], '截标开标时间须晚于技术标编制完成时间');
                }
//                check(!empty($base['charging_stage']) && !empty(trim($base['charging_stage'])), '请选择保证金收取阶段');
                check($base['bid_open_deadline'] > $base['supplier_invi_end_date'], '截标开标时间须晚于入围完成时间');
                check($base['bid_decision_date'] . ' 23:59:59' > $base['bid_open_deadline'], '定标日期须晚于截标开标时间');
                check($base['approach_date'] . ' 23:59:59' > $base['bid_decision_date'], '进场日期须晚于定标日期');
                break;
            case 'edited':
                if (empty($request['base'])) {
                    return $next($request);
                }
                $base = $request['base'];
                if ($base['bill_status'] === 'A') {
                    return $next($request);
                }
                check(!empty($base['name']) && !empty(trim($base['name'])), '请输入项目名称');
                check(!empty($base['bid_mode_id']) && !empty(trim($base['bid_mode_id'])), '请选择采购方式');
                check(!empty($base['invitation_deadline']) && !empty(trim($base['invitation_deadline'])), '请选择确认截止时间');
                check(!empty($base['doc_type']) && !empty(trim($base['doc_type'])), '请选择招标范围');
                check(!empty($base['qualification_required']) && !empty(trim($base['qualification_required'])), '请输入资质要求');
                check(!empty($base['bid_open_type']) && !empty(trim($base['bid_open_type'])), '请选择开标方式');
                check(!empty($base['evaluate_decide_way_id']) && !empty(trim($base['evaluate_decide_way_id'])), '请选择评定标方法');
                check(!empty($base['evaluated_method']) && !empty(trim($base['evaluated_method'])), '请选择评标方式');
                if ($base['evaluate_decide_way_id'] == '1') {
                    check(!empty($base['bid_eval_template']) && !empty(trim($base['bid_eval_template'])), '请选择评标模板');
                }
                $time = date('Y-m-d H:i:s');
                if (!empty($base['invitation_deadline'])) {
                    $base['invitation_deadline'] = date('Y-m-d H:i:s', strtotime($base['invitation_deadline']));
                }
                if (!empty($base['supplier_invi_end_date'])) {
                    $base['supplier_invi_end_date'] = date('Y-m-d H:i:s', strtotime($base['supplier_invi_end_date']));
                }
                if (!empty($base['commercial_doc_end_date'])) {
                    $base['commercial_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['commercial_doc_end_date']));
                }
                if (!empty($base['technical_doc_end_date'])) {
                    $base['technical_doc_end_date'] = date('Y-m-d H:i:s', strtotime($base['technical_doc_end_date']));
                }
                if (!empty($base['bid_open_deadline'])) {
                    $base['bid_open_deadline'] = date('Y-m-d H:i:s', strtotime($base['bid_open_deadline']));
                }
                if (!empty($base['bid_publish_date'])) {
                    $base['bid_publish_date'] = date('Y-m-d', strtotime($base['bid_publish_date']));
                }
                if (!empty($base['bid_evaluation_date'])) {
                    $base['bid_evaluation_date'] = date('Y-m-d', strtotime($base['bid_evaluation_date']));
                }
                if (!empty($base['bid_decision_date'])) {
                    $base['bid_decision_date'] = date('Y-m-d', strtotime($base['bid_decision_date']));
                }
                if (!empty($base['approach_date'])) {
                    $base['approach_date'] = date('Y-m-d', strtotime($base['approach_date']));
                }
                check($base['invitation_deadline'] > $time, '确认截止时间须晚于当前时间');
                check($base['supplier_invi_end_date'] > $base['invitation_deadline'], '入围完成时间须晚于确认截止时间');

                if ($base['evaluate_decide_way_id'] == '1') {
                    check($base['commercial_doc_end_date'] > $base['invitation_deadline'], '商务标编制完成时间须晚于确认截止时间');
                }
                if ($base['evaluate_decide_way_id'] == '1' && $base['doc_type'] == '1') {
                    check($base['technical_doc_end_date'] > $base['invitation_deadline'], '技术标编制完成时间须晚于确认截止时间');
                }
                check($base['bid_publish_date'] . ' 23:59:59' > $base['supplier_invi_end_date'], '发标日期须晚于入围完成时间');
                if ($base['evaluate_decide_way_id'] == '1') {
                    check($base['bid_publish_date'] . ' 23:59:59' > $base['commercial_doc_end_date'], '发标日期须晚于商务标编制完成时间');
                }
                if ($base['evaluate_decide_way_id'] == '1' && $base['doc_type'] == '1') {
                    check($base['bid_publish_date'] . ' 23:59:59' > $base['technical_doc_end_date'], '发标日期须晚于技术标编制完成时间');
                }
                check($base['bid_open_deadline'] > $base['bid_publish_date'], '截标开标时间须晚于发标日期');
                if ($base['evaluate_decide_way_id'] == '1') {
                    check($base['bid_open_deadline'] > $base['commercial_doc_end_date'], '截标开标时间须晚于商务标编制完成时间');
                }
                if ($base['evaluate_decide_way_id'] == '1' && $base['doc_type'] == '1') {
                    check($base['bid_open_deadline'] > $base['technical_doc_end_date'], '截标开标时间须晚于技术标编制完成时间');
                }
                check($base['bid_open_deadline'] > $base['supplier_invi_end_date'], '截标开标时间须晚于入围完成时间');
                check($base['bid_decision_date'] . ' 23:59:59' > $base['bid_open_deadline'], '定标日期须晚于截标开标时间');
                check($base['approach_date'] . ' 23:59:59' > $base['bid_decision_date'], '进场日期须晚于定标日期');
                break;
        }
        return $next($request);
    }

}
