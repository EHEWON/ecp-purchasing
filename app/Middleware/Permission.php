<?php

namespace App\Middleware;

use App\Common\Models\User;
use App\Modules\Admin\Repository\RolesRepo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Permission {

    /**
     * 不验证权限的api
     * @var array
     */
    protected $skip_auth = [
        '/admin/bidbill/offline/{id}',
        '/admin/bidbill/bindgroup/{id}',
        '/admin/bidbill/binduid/{id}',
        '/admin/bidbill/{id}',
        '/admin/bidmodes',
        '/admin/compare/notice',
        '/admin/country',
        '/admin/division',
        '/admin/divisionlevel',
        '/admin/index/inquiry/statistics',
        '/admin/index/quote/statistics',
        '/admin/index/statistics',
        '/admin/index/todo',
        '/admin/inquiry/entry/export/{id}',
        '/admin/inquiry/entry/import',
        '/admin/inquiry/notice/{id}',
        '/admin/inquiry/mulquote/{id}',
        '/admin/invoicetype',
        '/admin/invoicetype/group',
        '/admin/kingdee/announcementdetail/{id}',
        '/admin/kingdee/announcementlist',
        '/admin/kingdee/biddetail/{id}',
        '/admin/kingdee/grouplist',
        '/admin/kingdee/noticedetail/{id}',
        '/admin/kingdee/noticelist',
        '/admin/kingdee/search',
        '/admin/material/group',
        '/admin/notice/manage',
        '/admin/notice/manage/add',
        '/admin/notice/manage/audit',
        '/admin/notice/manage/cancel',
        '/admin/notice/manage/delete',
        '/admin/notice/manage/edited/{id}',
        '/admin/notice/manage/topping',
        '/admin/notice/manage/{id}',
        '/admin/paycond',
        '/admin/purchasers',
        '/admin/project/entry/import',
        '/admin/project/entry/template',
        '/admin/project/evaluation/info/{id}',
        '/admin/project/quote/{id}',
        '/admin/project/{group}/{quote_id}',
        '/admin/project/supplier/project',
        '/admin/eva/template/items/{id}',
        '/admin/purprojects',
        '/admin/purtypes',
        '/admin/valuationmodes',
        '/admin/quick/menu',
        '/admin/quick/menu/add',
        '/admin/quick/menu/delete/{id}',
        '/admin/supplier/add/{supplier_id}',
        '/admin/supplier/bidbill/bindgroup/{id}',
        '/admin/supplier/bidbill/binduid/{id}',
        '/admin/supplier/bidbill/offline/{id}',
        '/admin/supplier/project/cmfnotice/{id}',
        '/admin/supplier/project/{group}/{id}',
        '/admin/supplier/project/download/{group}/{id}',
        '/admin/supplier/evagrade',
        '/admin/supplier/grade',
        '/admin/supplier/group',
        '/admin/supplier/inquiry/export',
        '/admin/supplier/list',
        '/admin/supplier/quote/entry/export/{id}',
        '/admin/supplier/quote/entry/import',
        '/admin/supplier/template',
        '/admin/supplier/project/notice/{id}',
        '/admin/taxationsys',
        '/admin/taxcategory',
        '/admin/index/statistics',
        '/admin/index/todo',
        '/admin/taxrate',
        '/admin/usertype',
        '/admin/template/type'
    ];

    /**
     * 权限处理
     * @param Request $request
     * @param Closure $next
     * @return type
     */
    public function handle(Request $request, Closure $next) {
        // Auth认证
        $auth = Auth()->guard('admin');
        try {
            if (!$auth->check()) { //未登录踢回，给予错误返回提示
                Err('认证失败，请重新登录！', 401);
            }
            $admin = $auth->user();
            if (!$admin->status) {
                Auth::guard('admin')->logout();
                Err('账户已被禁用', 401);
            }
        } catch (TokenExpiredException $e) {
            Err($e->getMessage(), 401);
        } catch (TokenInvalidException $e) {
            Err($e->getMessage(), 401);
        } catch (JWTException $e) {
            Err($e->getMessage(), 401);
        }
        $user = User::selectRaw('user_id,user_type,phone,username,'
                        . 'email,image,realname,full_pinyin,birthday,'
                        . 'gender,password_flag,status,is_super,'
                        . 'sub,enable,deleted_flag')
                ->where('user_id', $admin->user_id)
                ->first();
        Auth()->guard('admin')->setUser($user);
        if (!$user) {
            Auth::guard('admin')->logout();
            Err('账户已被删除', 401);
        }
        if ($user->deleted_flag === 'Y') {
            Auth::guard('admin')->logout();
            Err('账户已被删除', 401);
        }
        if ($user->status == '0') {
            Auth::guard('admin')->logout();
            Err('账户已被禁用', 401);
        }
        if ($user->enable == '0') {
            Auth::guard('admin')->logout();
            Err('账户已被禁用', 401);
        }

        $this->checkPermission($auth->user());
        return $next($request);
    }

//
    /**
     * 权限验证
     * @return 
     */
    public function checkPermission($user) {
        $isSuper = $user->is_super;

        // 超管直接返回
        if ($isSuper == 1) {
            return;
        }

        $routes = app()->router->getRoutes();
        $currentRoute = app('request')->route();
        $uri = '';
        foreach ($routes as $route) {
            if (!empty($route['action']['uses']) && !empty($currentRoute[1]['uses']) && $route['action']['uses'] == $currentRoute[1]['uses']) {
                $uri = $route['uri'];
            }
        }
        $nuri = preg_replace('/\:[^}]+/', '', $uri);
        if (in_array(strtolower($nuri), $this->skip_auth)) {
            return;
        }
        $scope = (new RolesRepo)->getScopes($nuri);
    }

}
