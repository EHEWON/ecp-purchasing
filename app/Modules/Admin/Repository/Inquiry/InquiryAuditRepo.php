<?php

namespace App\Modules\Admin\Repository\Inquiry;

use App\Common\Contracts\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Common\Models\Inquiry\Inquiry;

class InquiryAuditRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'title',
        'biz_status',
        'bill_status',
        'bill_date',
        'end_date',
        'person_id',
    ];

    public function __construct() {
        $this->model = new Inquiry();
        parent::__construct($this->model);
    }

    /**
     * 待审核
     * @param  Request $request
     * @return array
     */
    public function pending(Request $request) {

        $params = $request->post();

        $admin = Auth::guard('admin')->user();

        $inquiry_status = $this->get_inquiry_status($admin);
        // print_r($inquiry_status);exit;
        if (empty($inquiry_status)) {
            return ['total' => 0, 'data' => []];
        }
        // print_r($inquiry_status);exit;
        if (count($inquiry_status) == 1) {
            $query = DB::table('inquiry', 'i')
                    ->select('id as inquiry_id', 'inquiry_no', 'inquiry_title', 'inquiry_type', 'i.created_at', 'u.realname', 'round', 'i.is_accurate')
                    ->join('user as u', 'u.user_id', '=', 'i.created_by')
                    ->where('i.deleted_flag', 'N')
                    ->whereIn('i.status', $inquiry_status);

            if (!$admin->is_super) {
                if ($inquiry_status[0] == Inquiry::STATUS_AUDIT_FIRST) {
                    $_data_user_id = $this->getViewUsers($admin->user_id, [$this->api_urls[0]]);
                } else if ($inquiry_status[0] == Inquiry::STATUS_AUDIT_SECOND) {
                    $_data_user_id = $this->getViewUsers($admin->user_id, [$this->api_urls[1]]);
                }
                if (!empty($_data_user_id)) {
                    $query->whereIn('i.created_by', $_data_user_id);
                }
            }
            // 国家
            if (!empty($params['country_id']) && is_array($params['country_id'])) {
                $query->whereIn('i.country_id', $params['country_id']);
            }
            // 行业
            if (!empty($params['industry_id']) && is_array($params['industry_id'])) {
                $query->whereIn('i.industry_id', $params['industry_id']);
            }
            // 经办人
            if (!empty($params['created_by']) && is_array($params['created_by'])) {
                $query->whereIn('i.created_by', $params['created_by']);
            }
            // 标题或编号
            if (!empty($params['title'])) {
                $query->where(function($q) use($params) {
                    $q->where('i.inquiry_title', 'like', '%' . $params['title'] . '%')
                            ->orWhere('i.inquiry_no', 'like', '%' . $params['title'] . '%');
                });
            }

            $clone = $query->clone();
            $total = $clone->select('1')->count();

            $page = $params['page'] ?? 1;
            $limit = $params['limit'] ?? 20;

            $query->offset(($page - 1) * $limit)->limit($limit);

            $query->orderBy('id', 'desc');

            return ['total' => $total, 'data' => $query->get()->toArray()];
        } else {

            $query = DB::table('inquiry', 'i')
                    ->select('id as inquiry_id', 'inquiry_no', 'inquiry_title', 'inquiry_type', 'i.created_at', 'u.realname', 'round')
                    ->join('user as u', 'u.user_id', '=', 'i.created_by')
                    ->where('i.deleted_flag', 'N')
                    ->where('i.status', Inquiry::STATUS_AUDIT_FIRST);

            if (!$admin->is_super) {
                $_data_user_id = $this->getViewUsers($admin->user_id, [$this->api_urls[0]]);
                // print_r($_data_user_id);exit;
                if (!empty($_data_user_id)) {
                    $query->whereIn('i.created_by', $_data_user_id);
                }
            }
            // 国家
            if (!empty($params['country_id']) && is_array($params['country_id'])) {
                $query->whereIn('i.country_id', $params['country_id']);
            }
            // 行业
            if (!empty($params['industry_id']) && is_array($params['industry_id'])) {
                $query->whereIn('i.industry_id', $params['industry_id']);
            }
            // 经办人
            if (!empty($params['created_by']) && is_array($params['created_by'])) {
                $query->whereIn('i.created_by', $params['created_by']);
            }
            // 标题或编号
            if (!empty($params['title'])) {
                $query->where(function($q) use($params) {
                    $q->where('i.inquiry_title', 'like', '%' . $params['title'] . '%')
                            ->orWhere('i.inquiry_no', 'like', '%' . $params['title'] . '%');
                });
            }

            $query2 = DB::table('inquiry', 'i')
                    ->select('id as inquiry_id', 'inquiry_no', 'inquiry_title', 'inquiry_type', 'i.created_at', 'u.realname', 'round')
                    ->join('user as u', 'u.user_id', '=', 'i.created_by')
                    ->where('i.deleted_flag', 'N')
                    ->where('i.status', Inquiry::STATUS_AUDIT_SECOND);

            if (!$admin->is_super) {
                $_data_user_id = $this->getViewUsers($admin->user_id, [$this->api_urls[1]]);
                // print_r($_data_user_id);exit;
                if (!empty($_data_user_id)) {
                    $query2->whereIn('i.created_by', $_data_user_id);
                }
            }
            // 国家
            if (!empty($params['country_id']) && is_array($params['country_id'])) {
                $query2->whereIn('i.country_id', $params['country_id']);
            }
            // 行业
            if (!empty($params['industry_id']) && is_array($params['industry_id'])) {
                $query2->whereIn('i.industry_id', $params['industry_id']);
            }
            // 经办人
            if (!empty($params['created_by']) && is_array($params['created_by'])) {
                $query2->whereIn('i.created_by', $params['created_by']);
            }
            // 标题或编号
            if (!empty($params['title'])) {
                $query2->where(function($q) use($params) {
                    $q->where('i.inquiry_title', 'like', '%' . $params['title'] . '%')
                            ->orWhere('i.inquiry_no', 'like', '%' . $params['title'] . '%');
                });
            }

            $query->union($query2);
            $clone = $query->clone();
            $total = $clone->count();
            $page = $params['page'] ?? 1;
            $limit = $params['limit'] ?? 20;
            $query->offset(($page - 1) * $limit)->limit($limit);
            $query->orderBy('inquiry_id', 'desc');
            $list = $query->get()->toArray();
            foreach ($list as &$item) {
                $item->audit_type = $item->round === 1 ? 'ADD' : 'MODIFY';
            }
            return ['total' => $total, 'data' => $list];
        }
    }

    /**
     * 历史审核
     * @param  Request $request
     * @return array
     */
    public function history(Request $request) {

        $params = $request->input();

        $admin = Auth::guard('admin')->user();

        $query = DB::table('inquiry_audit', 'a')
                ->select('a.id', 'a.inquiry_id', 'a.status', 'a.remark', 'a.user_id', 'a.level', 'i.created_by', 'i.inquiry_no', 'i.inquiry_title', 'i.inquiry_type', 'i.company_name', 'a.updated_at', 'a.created_at', 'u.username', 'u.realname', 'c.username as create_username', 'c.realname as create_realname', 'a.assessment', 'a.round', 'a.audit_type')
                ->where('a.deleted_flag', 'N')
                // ->where('a.user_id', $admin->user_id);
                ->whereIn('a.status', [InquiryAudit::STATUS_PASS, InquiryAudit::STATUS_REJECT])
                ->join('inquiry as i', 'i.id', '=', 'a.inquiry_id')
                ->join('user as u', 'u.user_id', '=', 'a.user_id', 'left')
                ->join('user as c', 'c.user_id', '=', 'i.created_by', 'left');
        // 商机ID
        if (!empty($params['inquiry_id'])) {
            $query->where('i.id', $params['inquiry_id']);
        }
        // 商机编号
        if (!empty($params['inquiry_no'])) {
            $query->where('i.inquiry_no', 'like', '%' . $params['inquiry_no'] . '%');
        }
        // 商机类型
        if (!empty($params['inquiry_type'])) {
            $query->where('i.inquiry_type', $params['inquiry_type']);
        }
        // 公司名称
        if (!empty($params['company_name'])) {
            $query->where('i.company_name', $params['company_name']);
        }
        // 审核类型
        if (!empty($params['level'])) {
            $query->whereBetween('a.level', $params['level']);
        }
        // 提交日期
        if (!empty($params['created_at'])) {
            $query->whereBetween('a.created_at', $params['created_at']);
        }
        // 审核日期
        if (!empty($params['updated_at'])) {
            $query->whereBetween('a.updated_at', $params['updated_at']);
        }
        // 状态
        if (!empty($params['status'])) {
            $query->where('a.status', $params['status']);
        }
        // 审核人
        if (!empty($params['user_id'])) {
            $query->where('a.user_id', $params['user_id']);
        }
        // 经办人
        if (!empty($params['created_by'])) {
            $query->where('i.created_by', $params['created_by']);
        }
        $clone = $query->clone();
        $total = $clone->select('1')->count();

        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;

        $query->offset(($page - 1) * $limit)->limit($limit);

        $query->orderBy('a.id', 'desc');

        return ['total' => $total, 'data' => $query->get()->toArray()];
    }

    /**
     * 获取审核记录信息
     *
     * @return array
     */
    public function detail(Request $request) {

        $id = $request->post('id');

        $audit = InquiryAudit::where('deleted_flag', 'N')->find($id);

        check(!empty($audit), Lang::get('response.no_data'));

        $user = User::select('username', 'realname', 'email', 'phone')->find($audit['user_id']);

        $audit = array_merge($audit->toArray(), $user->toArray());

        return $audit;
    }

    /**
     * 用户的审核记录
     * @param  Request $request
     * @return
     */
    public function customer(Request $request) {

        $params = $request->post();
        $customer_id = $params['customer_id'];

        $query = DB::table('customer_audit', 'a')->select('a.type', 'a.status', 'a.remark', 'a.created_at', 'u.realname')->join('user as u', 'a.audit_user_id', '=', 'u.user_id')->where('a.customer_id', $customer_id);

        $clone = $query->clone();
        $total = $clone->select('1')->count();

        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;

        $query->offset(($page - 1) * $limit)->limit($limit);

        $data = $query->orderBy('a.id', 'desc')->get()->toArray();

        return ['total' => $total, 'data' => $data];
    }

    /**
     * 获取待审核的次数
     * @return
     */
    public function count(Request $request) {

        $params = $request->post();

        $admin = Auth::guard('admin')->user();

        $inquiry_status = $this->get_inquiry_status($admin);
        // print_r($inquiry_status);exit;
        if (empty($inquiry_status)) {
            return ['total' => 0, 'has_role' => 0];
        }

        if (count($inquiry_status) == 1) {
            $query = DB::table('inquiry')
                    ->select('1')
                    ->where('deleted_flag', 'N')
                    ->whereIn('status', $inquiry_status);

            $user_id = $admin->getAuthIdentifier();
            $is_super = $admin->is_super;
            if ($is_super != 1) {
                $users = $this->getViewUsers($admin->user_id, $this->api_urls);
                if (!empty($users)) {
                    $query->whereIn('created_by', $users);
                }
            }

            return ['total' => $query->count(), 'has_role' => 1];
        } else {
            $query = DB::table('inquiry')
                    ->select('1')
                    ->where('deleted_flag', 'N')
                    ->where('status', Inquiry::STATUS_AUDIT_FIRST);

            $user_id = $admin->getAuthIdentifier();
            $is_super = $admin->is_super;
            if ($is_super != 1) {
                $users = $this->getViewUsers($admin->user_id, [$this->api_urls[0]]);
                if (!empty($users)) {
                    $query->whereIn('created_by', $users);
                }
            }

            $first_count = $query->count();

            $query2 = DB::table('inquiry')
                    ->select('1')
                    ->where('deleted_flag', 'N')
                    ->where('status', Inquiry::STATUS_AUDIT_SECOND);

            $user_id = $admin->getAuthIdentifier();
            $is_super = $admin->is_super;
            if ($is_super != 1) {
                $users = $this->getViewUsers($admin->user_id, [$this->api_urls[1]]);
                if (!empty($users)) {
                    $query2->whereIn('created_by', $users);
                }
            }

            $second_count = $query2->count();

            return ['total' => $first_count + $second_count, 'has_role' => 1];
        }
    }

    /**
     * 经办人列表(待审核中的)
     * @param  Request $request
     * @return
     */
    public function agents(Request $request) {

        $params = $request->post();

        $admin = Auth::guard('admin')->user();

        $inquiry_status = $this->get_inquiry_status($admin);
        // print_r($inquiry_status);exit;
        if (empty($inquiry_status)) {
            return [];
        }

        $query = DB::table('inquiry', 'i')
                ->select('u.realname', 'u.user_id')
                ->where('i.deleted_flag', 'N')
                ->whereIn('i.status', $inquiry_status)
                ->join('user as u', 'u.user_id', '=', 'i.created_by')
                ->groupBy('u.user_id');

        if (!empty($params['_data_user_id'])) {
            $query->whereIn('created_by', $params['_data_user_id']);
        }

        $data = $query->get()->toArray();

        return $data;
    }

    /**
     * 获取具有审核权限的用户
     * @return
     */
    private function get_inquiry_status($admin) {

        if ($admin->is_super) {
            return [Inquiry::STATUS_AUDIT_FIRST, Inquiry::STATUS_AUDIT_SECOND];
        }

        $user_id = $admin->user_id;

        $menus = AdminMenus::select('menu_id', 'api_url')
                        ->whereIn('api_url', $this->api_urls)
                        ->get()->toArray();

        $menus = array_column($menus, 'menu_id', 'api_url');

        $first_menu_id = $menus[$this->api_urls[0]] ?? 0; // 一级权限菜单id
        $second_menu_id = $menus[$this->api_urls[1]] ?? 0; //二级权限菜单id

        check(!empty($first_menu_id) && !empty($second_menu_id), Lang::get('index.permission_opportunity_menu'));
        // print_r($menus);exit;
        $user_roles = AdminWithRoles::select('role_id')
                        ->where('user_id', $user_id)
                        ->get()->toArray();

        $user_roles = array_column($user_roles, 'role_id');

        $first_role = AdminRolesWithMenus::select('role_id')
                ->where('menu_id', $first_menu_id)
                ->whereIn('role_id', $user_roles)
                ->count();

        $second_role = AdminRolesWithMenus::select('role_id')
                ->where('menu_id', $second_menu_id)
                ->whereIn('role_id', $user_roles)
                ->count();
        // print_r($first_role);
        $inquiry_status = [];

        if (!empty($first_role))
            $inquiry_status[] = Inquiry::STATUS_AUDIT_FIRST;
        if (!empty($second_role))
            $inquiry_status[] = Inquiry::STATUS_AUDIT_SECOND;

        return $inquiry_status;
    }

    private function getViewUsers($user_id, $api_urls) {

        $result = DB::table('admin_menus', 'm')
                ->select('rm.data_level')
                ->join('admin_role_with_menus as rm', 'rm.menu_id', '=', 'm.menu_id')
                ->join('admin_with_roles as ar', 'ar.role_id', '=', 'rm.role_id')
                ->join('admin_roles as r', 'r.role_id', '=', 'ar.role_id')
                ->where('ar.user_id', $user_id)
                ->where('r.is_check', 1)
                ->where('r.deleted_flag', 'N')
                ->whereIn('m.api_url', $api_urls)
                ->first();
        if (!empty($result)) {
            if ($result->data_level == 2) {
                return $this->getOrgUsers($user_id);
            } else if ($result->data_level == 3) {
                return [$user_id];
            }
        }
        return [];
    }

    /**
     * 获取部门用户
     * @return
     */
    private function getOrgUsers($user_id) {

        $org_users = OrgUser::where('user_id', $user_id)->get()->toArray();

        if (empty($org_users)) {
            return [$user_id];
        }
        $orgs = [];
        foreach ($org_users as $org_user) {
            $this->getSubOrg($org_user['org_id'], $orgs);
        }

        $users = OrgUser::select('user_id')->whereIn('org_id', array_unique($orgs))->get()->toArray();

        return array_column($users, 'user_id');
    }

    /**
     * 获取子部门
     * @return array
     */
    private function getSubOrg($orgId, &$data) {
        $data[] = $orgId;
        $orgs = Org::select('id', 'is_parent')
                ->where('parent_id', $orgId)
                ->where('deleted_flag', 'N')
                ->get()
                ->toArray();
        foreach ($orgs as $org) {
            $data[] = $org['id'];
            $org['is_parent'] && $this->getSubOrg($org['id'], $data);
        }
    }

}
