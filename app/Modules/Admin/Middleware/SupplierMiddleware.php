<?php

/**




 */

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Common\Models\{
    Supplier,
    User
};
use Illuminate\Support\Facades\Redis;

class SupplierMiddleware {

    public function handle(Request $request, Closure $next) {
        $currentRoute = app('request')->route()[1];
        list(, $action) = explode('@', $currentRoute['uses']);
        switch (strtolower($action)) {
            case 'register':
                if (empty(trim($request->name))) {
                    check(false, '企业名称不能为空');
                }
                $count = Supplier::where('name', trim($request->name))
                        ->count();
                if (!empty($count)) {
                    check(false, '企业名称已注册');
                }
                if (empty(trim($request->realname))) {
                    check(false, '姓名不能为空');
                }
                $isChinese = preg_match("/[\x{4e00}-\x{9fa5}]+/u", $request->realname);
                if ($isChinese) {
                    check(mb_strlen($request->realname) <= 127, '姓名限定128个字内');
                } else {
                    check(mb_strlen($request->realname) <= 255, '姓名限定128个字内');
                }
                if (empty(trim($request->phone_email))) {
                    check(false, '手机号或邮箱不能为空');
                }
                $phoneEmail = trim($request->phone_email);
                if (!isEmail($phoneEmail) && !is_mobile($phoneEmail)) {
                    check(false, '手机号或邮箱不正确');
                }
                $query = User::where(function($q)use($request) {
                            $q->where('phone', trim($request->phone_email))
                            ->orWhere('email', trim($request->phone_email));
                        })
                        ->where('deleted_flag', 'N');
                $exist = $query->first();
                check(empty($exist), '手机号或邮箱已存在');
                if (empty(trim($request->password))) {
                    check(false, '密码不能为空');
                }
                if (empty(trim($request->confirm_password))) {
                    check(false, '确认密码不能为空');
                }
                if (trim($request->confirm_password) != trim($request->password)) {
                    check(false, '密码不匹配');
                }
                if (empty(trim($request->verification_code))) {
                    check(false, '验证码不能为空');
                }
//                $countN = User::where('realname', trim($request->realname))
//                  ->where('deleted_flag', 'N')
//                  ->count();
//                if (!empty($countN)) {
//                    check(false, '姓名已注册');
//                }
                $code = Redis::get('register_supplier_code_' . trim($request->phone_email));
                $vCode = trim($request['verification_code']) ?? '';
                check(!empty($vCode) && strtolower($vCode) === strtolower(trim($code)), '请输入正确的手机号/邮箱验证码');
                break;
            case 'add':
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }
//                if (empty(trim($request->number))) {
//                    check(false, '企业编码不能为空');
//                }
                if (empty(trim($request->name))) {
                    check(false, '企业名称不能为空');
                }
                $count = Supplier::where('name', trim($request->name))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($count)) {
                    check(false, '企业名称已存在');
                }
                if (empty(trim($request->social_credit_code))) {
                    check(false, '统一社会信用代码不能为空');
                }
                if (empty(trim($request->reg_capital))) {
                    check(false, '注册资本不能为空');
                }
                if (empty(trim($request->scope_of_operation))) {
                    check(false, '经营范围不能为空');
                }
                $countN = Supplier::where('social_credit_code', trim($request->social_credit_code))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($countN)) {
                    check(false, '统一社会信用代码已存在');
                }
                if (empty($request->phone_email)) {
                    check(false, '请输入账号(手机号/邮箱)');
                }
                if (empty($request->realname)) {
                    check(false, '请输入系统管理员(名称)');
                }
                $phoneEmail = trim($request->phone_email);
                check(isEmail($phoneEmail) || is_mobile($phoneEmail), '账号(手机号/邮箱)不正确');
                $query = User::where(function($q)use($request) {
                            $q->where('phone', trim($request->phone_email))
                            ->orWhere('email', trim($request->phone_email));
                        })
                        ->where('deleted_flag', 'N');
                $exist = $query->first();
                check(empty($exist), '账号(手机号/邮箱)已存在');

                if (!empty($request->realname)) {
                    $isChinese = preg_match("/[\x{4e00}-\x{9fa5}]+/u", $request->realname);
                    check($isChinese ? mb_strlen($request->realname) <= 127 : mb_strlen($request->realname) <= 255, '姓名限定128个字内');

//                    $countN = User::where('realname', trim($request->realname))
//                      ->where('deleted_flag', 'N')
//                      ->count();
//
//                    check(empty($countN), '姓名已注册');
                }
                break;
            case 'edited':
                $query = Supplier::where('id', $request->id);
                $object = $query->first();
                if (empty($object)) {
                    check(false, '供应商不存在');
                }
                if ($request->status === Supplier::STATUS_DRAFT || $request->status === Supplier::STATUS_REVIEW) {
                    return $next($request);
                }

//                if (empty(trim($request->number))) {
//                    check(false, '编码不能为空');
//                }
                if (empty(trim($request->name))) {
                    check(false, '企业名称不能为空');
                }
                if (empty(trim($request->reg_capital))) {
                    check(false, '注册资本不能为空');
                }
                if (empty(trim($request->scope_of_operation))) {
                    check(false, '经营范围不能为空');
                }
                if (empty($object)) {
                    check(false, '企业不存在');
                }
                $count = Supplier::whereNot('id', $request->id)
                        ->where('name', trim($request->name))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($count)) {
                    check(false, '企业名称已存在');
                }
                $countN = Supplier::whereNot('id', $request->id)
                        ->where('social_credit_code', trim($request->social_credit_code))
                        ->where('deleted_flag', 'N')
                        ->count();
                if (!empty($countN)) {
                    check(false, '统一社会信用代码已存在');
                }
                break;
            case 'enable':
                if (empty($request->ids)) {
                    check(false, '请选择企业');
                }
                break;
            case 'disable':
                if (empty($request->ids)) {
                    check(false, '请选择企业');
                }
                break;
            case 'delete':
                if (empty($request->ids)) {
                    check(false, '请选择企业');
                }

                break;
            case 'phoneEmail':
                if (empty($request->phone_email)) {
                    check(false, '请输入邮箱或手机号');
                }
                break;
        }
        return $next($request);
    }

}
