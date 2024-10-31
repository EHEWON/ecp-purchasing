<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use App\Common\Models\{
    Purchaser,
    Supplier,
    SupplierAudit,
    User,
    UserContact,
    UserPurchaser,
    UserSupplier
};
use App\Modules\Admin\{
    Events\AdminLoginLogEvent,
    Middleware\CheckIpBlacklist,
    Repository\MenusRepo,
    Repository\OrgRepo,
    Repository\PermissionsRepo,
    Repository\RolesRepo,
    Repository\SupplierContactRepo,
    Repository\UserLoginRepo,
    Repository\UserPurchaserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    DB,
    Lang,
    Redis
};

class AuthService extends Service {

    protected $guard = 'admin';
    public $middleware = [
        CheckIpBlacklist::class => []
    ];
    public $beforeEvent = [];
    public $afterEvent = [
        AdminLoginLogEvent::class => []
    ];

    public function getRules() {
        return [
            'login' => [
                'username' => 'required',
                'password' => 'required'
            ],
            'change' => [
                'oldpwd' => 'required',
                'password' => 'required|confirmed|min:6'
            ],
            'avatar' => [
                'image' => 'required',
            ]
        ];
    }

    public function getMessages() {
        return [
            'login' => [
                'username.required' => Lang::get('customer.login_name_required'),
                'password.required' => Lang::get('customer.password_required'),
            ],
            'change' => [
                'oldpwd.required' => Lang::get('user.enter_old_password'),
                'password.required' => Lang::get('user.enter_new_password'),
                'password.confirmed' => Lang::get('user.new_password_invalid'),
                'password.min' => Lang::get('user.password_length_min')
            ],
            'avatar' => [
                'image.required' => Lang::get('user.upload_avatar'),
            ]
        ];
    }

    public function login(Request $request) {
        $auth = Auth::guard($this->guard);
        $username = $request->input('username');
        $expire = config('admin.token_exp');
        $token = $auth->setTTL($expire)->attempt([
            function ($qd)use($username) {
                $qd->where('deleted_flag', 'N')
                        ->where(function ($q)use($username) {
                                    $q->where('username', $username)
                                    ->orWhere('email', $username);
                                });
            },
            'password' => $request->input('password')
        ]);
        check(!empty($token), Lang::get('user.account_error'));
        $admin = $auth->user();
        check(!empty($admin), Lang::get('user.account_error'));
        check($admin->status != 0, Lang::get('user.account_disabled'));
        check($admin->enable != 0, Lang::get('user.account_disabled'));
        check($admin->deleted_flag == 'N', Lang::get('user.account_no_exist'));
        $userContactsObj = UserContact::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->get();
        if (!empty($userContactsObj)) {
            $userContacts = $userContactsObj->toArray();
        }
        $userSupplierObj = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->first();

        if (!empty($userSupplierObj)) {
            $userSupplier = $userSupplierObj->toArray();
            $supplierId = $userSupplier['supplier_id'];
            $supplier = Supplier::where('id', $supplierId)
                    ->where('deleted_flag', 'N')
                    ->select('id', 'name', 'status', 'enable')
                    ->first();
            check(!empty($supplier), Lang::get('supplier.supplier_not_existence'));
            $audit = SupplierAudit::selectRaw('id,supplier_id,status,audit_type')
                    ->where('supplier_id', $supplierId)
                    ->whereIn('audit_type', ['CHANGE'])
                    ->where('status', 'REVIEW')
                    ->first();
            $userSupplier['audit_change'] = !empty($audit) ? $audit->status : null;
            $userSupplier['audit_change_id'] = !empty($audit) ? $audit->id : null;
            $userSupplier['id'] = !empty($supplier) ? $supplier->id : null;
            $userSupplier['supplier_name'] = !empty($supplier) ? $supplier->name : null;
            $userSupplier['supplier_status'] = !empty($supplier) ? $supplier->status : null;
            $userSupplier['supplier_enable'] = !empty($supplier) ? $supplier->enable : null;
            if (!empty($supplier) && $supplier === 'APPROVED' && $supplier->enable != '1') {
                check(false, Lang::get('supplier.supplier_has_been_frozen'));
            }
        }

        $menus_repo = new MenusRepo();
        $roles_repo = new RolesRepo();
        $perm_repo = new PermissionsRepo();
        if ($admin->user_type == 'PLATFORM') {
            $menus = $menus_repo->getMenusByUser($admin->user_type, ['type' => 'MENU']);
            $roles_ids = $roles_repo->getRolesByUser($admin->user_type);
            $perm_method = $perm_repo->getPermsByRolesIds($roles_ids);

            Redis::setex('role_' . $admin->user_type, $expire, json_encode($roles_ids));
            Redis::setex('perm_' . $admin->user_type, $expire, json_encode($perm_method));
            $userPurchaser = new UserPurchaserRepo();
            $departments = $userPurchaser->getUserOrgIds($admin->user_id);
            Redis::setex('departments_' . $admin->user_type . '_' . $token, $expire, json_encode($departments));
        } elseif ($admin->user_type == 'SUPPLIER' && !empty($userSupplier)) {
            $menus = $menus_repo->getMenusByUser($admin->user_type, ['type' => 'MENU']);
            $roles_ids = $roles_repo->getRolesByUser($admin->user_type);
            $perm_method = $perm_repo->getPermsByRolesIds($roles_ids);
            Redis::setex('cur_pid' . $token, $expire, $userSupplier['id']);
            Redis::setex('role_' . $admin->user_type . '_' . $token, $expire, json_encode($roles_ids));
            Redis::setex('perm_' . $admin->user_type . '_' . $token, $expire, json_encode($perm_method));
        }
        return [
            'user_id' => $admin->user_id,
            'is_super' => $admin->is_super,
            'user_type' => $admin->user_type,
            'username' => $admin->username,
            'phone' => $admin->phone,
            'email' => $admin->email,
            'realname' => $admin->realname,
            'full_pinyin' => $admin->full_pinyin,
            'birthday' => $admin->birthday,
            'gender' => $admin->gender,
            'image' => $admin->image,
            'password_flag' => $admin->password_flag,
            'access_token' => $token,
            'token_type' => 'Bearer',
            "cur_purchaser" => [
                'id' => '1',
                'purchaser_type' => 'PLATFORM',
                'name' => '瑞招采平台'
            ],
            'menus' => !empty($menus) ? $menus : [],
            'roles_ids' => !empty($roles_ids) ? $roles_ids : [],
            'perm_method' => !empty($perm_method) ? $perm_method : [],
            'user_contacts' => !empty($userContacts) ? $userContacts : [],
            'user_supplier' => !empty($userSupplier) ? $userSupplier : [],
            'expires_time' => time() + Auth::guard($this->guard)->factory()->getTTL() * 3600 * 24
        ];
    }

    public function bossLogin(Request $request) {
        $auth = Auth::guard($this->guard);
        $username = $request->input('username');
        $expire = config('admin.token_exp');
        check(!empty($username), '账号不能为空');
        $token = ($user = Auth::getProvider()->retrieveByCredentials([
            function ($qd)use($username) {
                $qd->where('deleted_flag', 'N')
                        ->where(function ($q)use($username) {
                                    $q->where('email', $username)
                                    ->orWhere('phone', $username)
                                    ->orWhere('username', $username);
                                });
            },
                ])) ? Auth::login($user) : false;
        check(!empty($token), Lang::get('user.account_error'));
        $admin = $auth->user();
        check(!empty($admin), Lang::get('user.account_error'));
        check($admin->status != 0, Lang::get('user.account_disabled'));
        check($admin->enable != 0, Lang::get('user.account_disabled'));
        check($admin->deleted_flag == 'N', Lang::get('user.account_no_exist'));
        (new UserLoginRepo)->addLog($admin->user_id);
        $userContactsObj = UserContact::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->get();
        if (!empty($userContactsObj)) {
            $userContacts = $userContactsObj->toArray();
        }
        $userSupplierObj = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->first();

        if (!empty($userSupplierObj)) {
            $userSupplier = $userSupplierObj->toArray();
            $supplierId = $userSupplier['supplier_id'];
            $supplier = Supplier::where('id', $supplierId)
                    ->where('deleted_flag', 'N')
                    ->select('id', 'name', 'status', 'enable')
                    ->first();
            check(!empty($supplier), Lang::get('supplier.supplier_not_existence'));
            $audit = SupplierAudit::selectRaw('id,supplier_id,status,audit_type')
                    ->where('supplier_id', $supplierId)
                    ->whereIn('audit_type', ['CHANGE'])
                    ->where('status', 'REVIEW')
                    ->first();
            $userSupplier['audit_change'] = !empty($audit) ? $audit->status : null;
            $userSupplier['audit_change_id'] = !empty($audit) ? $audit->id : null;
            $userSupplier['id'] = !empty($supplier) ? $supplier->id : null;
            $userSupplier['supplier_name'] = !empty($supplier) ? $supplier->name : null;
            $userSupplier['supplier_status'] = !empty($supplier) ? $supplier->status : null;
            $userSupplier['supplier_enable'] = !empty($supplier) ? $supplier->enable : null;
            if (!empty($supplier) && $supplier === 'APPROVED' && $supplier->enable != '1') {
                check(false, Lang::get('supplier.supplier_has_been_frozen'));
            }
        }

        $menus_repo = new MenusRepo();
        $roles_repo = new RolesRepo();
        $perm_repo = new PermissionsRepo();
        if (in_array($admin->user_type, ['PURCHASER', 'PLATFORM', 'ORG'])) {
            $menus = $menus_repo->getMenusByUser($admin->user_type, ['type' => 'MENU']);
            $roles_ids = $roles_repo->getRolesByUser($admin->user_type);
            $perm_method = $perm_repo->getPermsByRolesIds($roles_ids);
            Redis::setex('role_' . $admin->user_type, $expire, json_encode($roles_ids));
            Redis::setex('perm_' . $admin->user_type, $expire, json_encode($perm_method));
        }
        if ($admin->user_type == 'SUPPLIER' && !empty($userSupplier)) {
            $menus = $menus_repo->getMenusByUser($admin->user_type, ['type' => 'MENU']);
            $roles_ids = $roles_repo->getRolesByUser($admin->user_type);
            $perm_method = $perm_repo->getPermsByRolesIds($roles_ids);
            Redis::setex('cur_pid' . $token, $expire, $userSupplier['id']);
            Redis::setex('role_' . $admin->user_type, $expire, json_encode($roles_ids));
            Redis::setex('perm_' . $admin->user_type, $expire, json_encode($perm_method));
        }
        return [
            'user_id' => $admin->user_id,
            'is_super' => $admin->is_super,
            'user_type' => $admin->user_type,
            'username' => $admin->username,
            'phone' => $admin->phone,
            'email' => $admin->email,
            'realname' => $admin->realname,
            'full_pinyin' => $admin->full_pinyin,
            'birthday' => $admin->birthday,
            'gender' => $admin->gender,
            'image' => $admin->image,
            'password_flag' => $admin->password_flag,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'menus' => !empty($menus) ? $menus : [],
            'roles_ids' => !empty($roles_ids) ? $roles_ids : [],
            'perm_method' => !empty($perm_method) ? $perm_method : [],
            'user_contacts' => !empty($userContacts) ? $userContacts : [],
            'user_supplier' => !empty($userSupplier) ? $userSupplier : [],
            'user_purchaser' => [],
            'cur_purchaser' => [
                'id' => '1',
                'purchaser_type' => 'PLATFORM',
                'name' => '瑞招采平台'
            ],
            'expires_time' => time() + Auth::guard($this->guard)->factory()->getTTL() * 3600 * 24
        ];
    }

    /**
     * 当前登录用户信息
     * @return
     */
    public function info(Request $request) {
        $admin = Auth::guard($this->guard)->user();
        $userId = $admin->user_id;
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $table = (new User)->getTable();
        $query = User::from($table . ' as u')
                ->selectRaw('u.user_id,u.user_type,u.phone,u.username,u.email,u.image,'
                . 'u.realname,u.full_pinyin,u.birthday,u.gender,'
                . 'u.status,u.is_super,u.sub,u.created_at,u.updated_at');
        $query->where('u.user_id', $userId);
        $userinfo = $query->first();
        $userSupplierObj = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->first();

        if (!empty($userSupplierObj)) {
            $userSupplier = $userSupplierObj->toArray();
            $supplierId = $userSupplier['supplier_id'];
            $supplier = Supplier::where('id', $supplierId)
                    ->where('deleted_flag', 'N')
                    ->select('id', 'name', 'status', 'enable')
                    ->first();
            $default = (new SupplierContactRepo())->getDefaultContact($supplierId);

            $audit = SupplierAudit::selectRaw('id,supplier_id,status,audit_type')
                    ->where('supplier_id', $supplierId)
                    ->whereIn('audit_type', ['CHANGE'])
                    ->where('status', 'REVIEW')
                    ->first();
            $userSupplier['audit_change'] = !empty($audit) ? $audit->status : null;
            $userSupplier['audit_change_id'] = !empty($audit) ? $audit->id : null;
            $userSupplier['id'] = !empty($supplier) ? $supplier->id : null;
            $userSupplier['supplier_name'] = !empty($supplier) ? $supplier->name : null;
            $userSupplier['supplier_status'] = !empty($supplier) ? $supplier->status : null;
            $userSupplier['supplier_enable'] = !empty($supplier) ? $supplier->enable : null;
            $userSupplier['contact_name'] = !empty($default['contact_name']) ? $default['contact_name'] : null;
            $userSupplier['contact_phone'] = !empty($default['phone']) ? $default['phone'] : null;
            $userSupplier['contact_email'] = !empty($default['email']) ? $default['email'] : null;
        }
        $userPurchaserObj = UserPurchaser::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->get();
        if (!empty($userPurchaserObj)) {
            $userPurchasers = $userPurchaserObj->toArray();
            (new OrgRepo)->setOrgs($userPurchasers);
        }
        $userinfo['user_supplier'] = !empty($userSupplier) ? $userSupplier : new \stdClass();
        $userinfo['user_purchaser'] = !empty($userPurchasers) ? $userPurchasers : [];
        $userinfo['cur_purchaser'] = [
            'id' => '1',
            'purchaser_type' => 'PLATFORM',
            'name' => '瑞招采平台'
        ];
        return $userinfo;
    }

    /**
     * 修改密码
     * @return
     */
    public function change(Request $request) {

        $params = $request->post();

        $oldpwd = $params['oldpwd'];
        $password = $params['password'];

        $admin = Auth::guard($this->guard)->user();
        $user_id = $admin->user_id;

        $userinfo = User::find($user_id);
        check(password_verify($oldpwd, $userinfo['password']), Lang::get('user.old_password_invalid'));

        User::where('user_id', $user_id)->update(['password' => password_hash($password, PASSWORD_DEFAULT), 'password_flag' => 0]);

        return [];
    }

    /**
     * 修改头像
     * @return
     */
    public function avatar(Request $request) {

        $params = $request->post();
        $image = $params['image'];

        $admin = Auth::guard($this->guard)->user();
        $user_id = $admin->user_id;

        User::where('user_id', $user_id)->update(['image' => $image]);

        return [];
    }

    /**
     * 登录管理员信息获取
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \App\Exceptions\Admin\AuthTokenException
     */
    public function me(Request $request) {
        if (!$admin = Auth::guard($this->guard)->user()) {
            Err('TOKEN_EXP');
        }
        $admin['roles'] = [];
        $authorization = Auth::guard('admin')->getToken();
        $redisKey = md5($authorization);
        $curId = '';
        if (!empty($authorization) && Redis::command('exists', [$redisKey])) {
            $curId = Redis::get($redisKey);
        }
        $userType = $admin->user_type;
        $query = DB::table('menus', 'm')
                ->selectRaw('p.route,m.sort,m.type,m.path,m.perm_id,p.type as perm_type,p.is_share')
                ->join('permissions as p', 'p.id', '=', 'm.perm_id')
                ->where('p.status', 'NORMAL')
                ->where('m.status', 'NORMAL')
                ->where('m.deleted_flag', 'N')
                ->where('p.deleted_flag', 'N');

        if ($userType === 'SUPPLIER') {
            $query->whereIn('m.menu_type', ['SUPPLIER', 'SYSTEM', 'COMMON'])
                    ->whereIn('p.permission_type', ['SUPPLIER', 'SYSTEM', 'COMMON']);
        } else {
            $query->whereIn('m.menu_type', ['PURCHASER', 'COMMON', 'SYSTEM', 'PLATFORM'])
                    ->whereIn('p.permission_type', ['PURCHASER', 'COMMON', 'SYSTEM', 'PLATFORM']);
        }
        $adminRoles = $query->groupBy('route')
                ->orderBy('route', 'ASC')
                ->pluck('route');
        $admin['roles'] = $adminRoles;
        $userContactsObj = UserContact::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->get();
        if (!empty($userContactsObj)) {
            $userContacts = $userContactsObj->toArray();
        }
        $userSupplierObj = UserSupplier::where('user_id', $admin->user_id)
                ->where('deleted_flag', 'N')
                ->first();
        if (!empty($userSupplierObj)) {
            $userSupplier = $userSupplierObj->toArray();
            $supplierId = $userSupplier['supplier_id'];
            $supplier = Supplier::where('id', $supplierId)
                    ->where('deleted_flag', 'N')
                    ->select('id', 'name', 'status', 'enable')
                    ->first();
            $audit = SupplierAudit::selectRaw('id,supplier_id,status,audit_type')
                    ->where('supplier_id', $supplierId)
                    ->whereIn('audit_type', ['CHANGE'])
                    ->where('status', 'REVIEW')
                    ->first();
            $userSupplier['audit_change'] = !empty($audit) ? $audit->status : null;
            $userSupplier['audit_change_id'] = !empty($audit) ? $audit->id : null;
            $userSupplier['id'] = !empty($supplier) ? $supplier->id : null;
            $userSupplier['supplier_name'] = !empty($supplier) ? $supplier->name : null;
            $userSupplier['supplier_status'] = !empty($supplier) ? $supplier->status : null;
            $userSupplier['supplier_enable'] = !empty($supplier) ? $supplier->enable : null;
        }

        $admin['user_contacts'] = !empty($userContacts) ? $userContacts : [];
        $admin['user_supplier'] = !empty($userSupplier) ? $userSupplier : new \stdClass();
        $admin['user_purchaser'] = [];
        $menus_repo = new MenusRepo();
        $roles_repo = new RolesRepo();
        $perm_repo = new PermissionsRepo();
        $expire = 86400;
        if ($admin->user_type == 'PLATFORM') {
            $admin['menus'] = $menus_repo->getMenusByUser($admin->user_type, ['type' => 'MENU']);
            $roles_ids = $roles_repo->getRolesByUser($admin->user_type);
            $admin['perm_method'] = $perm_repo->getPermsByRolesIds($roles_ids);
            Redis::setex('perm_' . $admin->user_type, $expire, json_encode($admin['perm_method']));
            Redis::setex('role_' . $admin->user_type, $expire, json_encode($roles_ids));
            $userPurchaser = new UserPurchaserRepo();
            $departments = $userPurchaser->getUserOrgIds($admin->user_id, $curId);
            Redis::setex('departments_' . $admin->user_type, $expire, json_encode($departments));
            $admin['cur_purchaser'] = [
                'id' => '1',
                'purchaser_type' => 'PLATFORM',
                'name' => '瑞招采平台'
            ];
        }

        if ($admin->user_type == 'SUPPLIER') {
            $admin['menus'] = $menus_repo->getMenusByUser($admin->user_type, ['type' => 'MENU']);
            $roles_ids = $roles_repo->getRolesByUser($admin->user_type);
            $admin['perm_method'] = $perm_repo->getPermsByRolesIds($roles_ids);
            Redis::setex('cur_pid' . $authorization, $expire, $userSupplier['id']);
            Redis::setex('role_' . $admin->user_type, $expire, json_encode($roles_ids));
            Redis::setex('perm_' . $admin->user_type, $expire, json_encode($admin['perm_method']));
        }
        return $admin;
    }

    /**
     * 获取拥有的权限
     *
     * @throws \App\Exceptions\Admin\AuthTokenException
     */
    public function getRabcList() {
        if (!$admin = Auth::guard($this->guard)->user()) {
            Err('TOKEN_EXP');
        }
// 如果是admin_id = 1，那么默认返回全部权限
//        if($admin->user_id == 1){
//            return list_to_tree($this->adminMenusRep->getAllMenus());
//        }
        $admin = User::with(['roles.menus'])->find($admin->user_id)->toArray();

        $menus = [];
        foreach (array_column($admin['roles'], 'menus') as $item) {
            $menus = array_merge($menus, $item);
        }

        return list_to_tree($menus);
    }

    /**
     * 退出登录
     *
     * @return bool
     */
    public function logout() {
        Auth::guard($this->guard)->logout();
        return true;
    }

    /**
     * Refresh a token.
     * 刷新token，如果开启黑名单，以前的token便会失效。
     * 值得注意的是用上面的getToken再获取一次Token并不算做刷新，两次获得的Token是并行的，即两个都可用。
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->respondWithToken(auth($this->guard)->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param $token
     * @return array
     */
    protected function respondWithToken($token) {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_time' => time() + Auth::guard($this->guard)->factory()->getTTL() * 3600 * 24
        ];
    }

    public function purchasers() {
        if (!$admin = Auth::guard($this->guard)->user()) {
            Err('TOKEN_EXP');
        }
        $userType = $admin->user_type;
        switch ($userType) {
            case 'SUPPLIER':
                $userSupplierObj = UserSupplier::where('user_id', $admin->user_id)
                        ->where('deleted_flag', 'N')
                        ->first();
                if (empty($userSupplierObj)) {
                    return [];
                }
                $supplierId = $userSupplierObj->supplier_id;
                return Supplier::where('id', $supplierId)
                                ->where('deleted_flag', 'N')
                                ->where('enable', '1')
                                ->selectRaw('id,name,status,enable,supplier_no AS number')
                                ->first();
            case 'PLATFORM':
            case 'ORG':
            case 'PURCHASER':
                $orgIds = UserPurchaser::where('user_id', $admin->user_id)
                        ->where('deleted_flag', 'N')
                        ->pluck('bot_purchaser_id');
                if (empty($orgIds)) {
                    return [];
                }
                $obejct = Purchaser::whereIn('id', $orgIds)
                        ->where('deleted_flag', 'N')
                        ->where('enable', '1')
                        ->whereIn('purchaser_type', ['PURCHASER', 'PLATFORM'])
                        ->selectRaw('id, name, status,purchaser_type,number')
                        ->get();
                return empty($obejct) ? [] : $obejct->toArray();
        }
    }

}
