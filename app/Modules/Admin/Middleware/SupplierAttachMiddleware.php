<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Supplier;

class SupplierAttachMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
                if (empty($request->attach)) {
                    check(false, '附件信息不能为空');
                }
                $attachs = [];
                foreach ($request->attach as $attachType => $attachArr) {
                    foreach ($attachArr as $attach) {
                        if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                            continue;
                        }
                        if (empty($attach['attach_type'])) {
                            $attach['attach_type'] = 'OTHER';
                        }
                        $attachs[$attach['attach_type']][] = $attach;
                    }
                }
                if (empty($attachs)) {
                    check(false, '附件信息不能为空');
                }
                if (empty($attachs['BUSINESS_LICENSE'])) {
                    check(false, '请上传营业执照');
                }
                if (empty($attachs['LEGAL_PERSON_ID2'])) {
                    check(false, '请上传身份证(头像面)');
                }
                if (empty($attachs['LEGAL_PERSON_ID1'])) {
                    check(false, '上传身份证(国徽面)');
                }
                break;
            case 'edited':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
                if (empty($request->attach)) {
                    check(false, '附件信息不能为空');
                }
                $attachs = [];
                foreach ($request->attach as $attachType => $attachArr) {
                    foreach ($attachArr as $attach) {
                        if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                            continue;
                        }
                        if (empty($attach['attach_type'])) {
                            $attach['attach_type'] = 'OTHER';
                        }
                        $attachs[$attach['attach_type']][] = $attach;
                    }
                }
                if (empty($attachs)) {
                    check(false, '附件信息不能为空');
                }
                if (empty($attachs['BUSINESS_LICENSE'])) {
                    check(false, '请上传营业执照');
                }

                if (empty($attachs['LEGAL_PERSON_ID1'])) {
                    check(false, '上传身份证(国徽面)');
                }
                if (empty($attachs['LEGAL_PERSON_ID2'])) {
                    check(false, '请上传身份证(头像面)');
                }
                break;
        }
        return $next($request);
    }

}
