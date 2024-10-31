<?php

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\Supplier;

class SupplierContactMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'add':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
                if (empty($request->contact)) {
                    check(false, '联系人信息不能为空');
                }
                $contacts = [];
                foreach ($request->contact as $contact) {
                    if (empty($contact['contact_name']) && empty($contact['phone']) && empty($contact['email'])) {
                        continue;
                    }
                    if (empty($contact['contact_name'])) {
                        check(false, '联系人姓名不能为空');
                    }
                    if (empty($contact['phone'])) {
                        check(false, '联系人手机号不能为空');
                    }
                    if (empty($contact['email'])) {
                        check(false, '联系人邮箱不能为空');
                    }
                    $contacts[] = $contact;
                }
                if (empty($contacts)) {
                    check(false, '联系人信息不能为空');
                }
                break;
            case 'edited':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
                if (empty($request->contact)) {
                    check(false, '联系人信息不能为空');
                }
                $contacts = [];
                foreach ($request->contact as $contact) {
                    if (empty($contact['contact_name']) && empty($contact['phone']) && empty($contact['email'])) {
                        continue;
                    }
                    if (empty($contact['contact_name'])) {
                        check(false, '联系人姓名不能为空');
                    }
                    if (empty($contact['phone'])) {
                        check(false, '联系人手机号不能为空');
                    }
                    if (empty($contact['email'])) {
                        check(false, '联系人邮箱不能为空');
                    }
                    $contacts[] = $contact;
                }
                if (empty($contacts)) {
                    check(false, '联系人信息不能为空');
                }
                break;
        }
        return $next($request);
    }

}
