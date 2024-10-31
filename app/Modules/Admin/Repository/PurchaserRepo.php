<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Helpers\Excel;
use App\Common\Models\{
    Purchaser,
    PurchaserBusiness,
    User,
    UserContacts,
    UserPurchaser
};
use App\Modules\Admin\Repository\CountryRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,
    Mail
};
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Style\Alignment,
    Style\Border,
    Style\Font
};

class PurchaserRepo extends Repository {

    protected $model;
    protected $sorts = [
        'number',
        'name',
        'long_name',
        'enable',
        'disabled_at',
        'contact_name',
        'created_at',
    ];

    public function __construct() {
        $this->model = new Purchaser();
        parent::__construct($this->model);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request, $filed = 'id,number,name,contact_name,'
    . 'contact_phone,contact_email,created_by,created_at,updated_by,'
    . 'updated_at,province_id,city_id,enable,parent_id') {

        $table = $this->model->getTable();
        $query = $this->model->from($table . ' as p')->selectRaw($filed);
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        (new DivisionRepo)->setDivisions($data, 'province_id', 'province');
        (new DivisionRepo)->setDivisions($data, 'city_id', 'city');
        $this->setPurchasers($data, 'parent_id', 'parent_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getAll(Request $request, $filed = 'id,number,name,contact_name,'
    . 'contact_phone,contact_email,created_by,created_at,updated_by,'
    . 'updated_at,province_id,city_id,enable,parent_id') {

        $table = $this->model->getTable();
        $query = $this->model->from($table . ' as p')
                ->selectRaw($filed);
        $this->getWhere($query, $request);
        $query->whereIn('purchaser_type', 'PURCHASER');
        $query->where('status', 'APPROVED');
        $query->where('enable', '1');
        $query->where('deleted_flag', 'N');
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        (new DivisionRepo)->setDivisions($data, 'province_id', 'province');
        (new DivisionRepo)->setDivisions($data, 'city_id', 'city');
        $this->setPurchasers($data, 'parent_id', 'parent_name');
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? 'p.' . trim($request->sort) : 'p.created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';

        $query->orderBy($sort, $order);
        if ($sort !== 'created_at') {
            $query->orderBy('p.created_at', 'DESC');
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function tree(Request $request, $filed = 'id,number,name,contact_name,'
    . 'contact_phone,contact_email,created_by,parent_id,enable,'
    . 'created_at,updated_by,updated_at,province_id,city_id') {
        $table = $this->model->getTable();
        $query = $this->model->from($table . ' as p')
                ->selectRaw('id,parent_ids');
        $this->getWhere($query, $request);
        $query->where('enable', 1);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        $ids = [];
        foreach ($list as $item) {
            $ids[] = $item['id'];
            if (!empty($item['parent_ids'])) {
                $ids = array_merge($ids, explode(',', $item['parent_ids']));
            }
        }
        $uids = array_unique($ids);
        $dquery = $this->model->from($table . ' as p')
                ->selectRaw($filed);
        $dquery->where('enable', 1);
        $dquery->whereIn('id', $uids);
        $this->getOrder($dquery, $request);
        $dataObject = $dquery->get();
        if (empty($dataObject)) {
            return [];
        }
        $data = $dataObject->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        (new DivisionRepo)->setDivisions($data, 'province_id', 'province');
        (new DivisionRepo)->setDivisions($data, 'city_id', 'city');
        $this->setPurchasers($data, 'parent_id', 'parent_name');
        return (new OrgRepo())->handelTree($data, 0);
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function userTree(Request $request, $filed = 'id,number,name,contact_name,'
    . 'contact_phone,contact_email,created_by,parent_id,enable,'
    . 'created_at,updated_by,updated_at,province_id,city_id') {
        $table = $this->model->getTable();
        $query = $this->model->from($table . ' as p')->selectRaw($filed);
        $this->getWhere($query, $request);
        $query->where('enable', 1);
        $this->getOrder($query, $request);
        $object = $query->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        (new DivisionRepo)->setDivisions($data, 'province_id', 'province');
        (new DivisionRepo)->setDivisions($data, 'city_id', 'city');
        $this->setPurchasers($data, 'parent_id', 'parent_name');
        return (new OrgRepo())->handelTree($data, 0);
    }


    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {

        $table = $this->model->getTable();
        $query = $this->model
                ->from($table . ' as p')
                ->selectRaw('p.id,p.number,p.parent_id,p.parent_ids,p.enable,p.name,p.contact_name,'
                . 'p.contact_phone,p.contact_email,p.describe,p.province_id,p.long_name,'
                . 'p.city_id,p.contact_address,p.created_by,p.created_at,'
                . 'p.updated_by,p.updated_at,p.deleted_flag');
        $query->where('p.id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        if (!empty($data['parent_ids'])) {
            $data['parent_ids'] = explode(',', $data['parent_ids']);
        } else {
            $data['parent_ids'] = [];
        }
        return $data;
    }

    /**
     * @param int $id
     * @param Request $request
     * 
     * @return array
     */
    public function edited($id, Request $request) {
        $admin = Auth::guard('admin')->user();
        $parentId = !empty($request->parent_id) ? intval($request->parent_id) : 1;
        $longName = Purchaser::where('id', $parentId)->value('long_name');
        $parentIds = Purchaser::where('id', $parentId)->value('parent_ids');
        Purchaser::where('id', $id)->update([
            'purchaser_type' => 'PURCHASER',
            'number' => trim($request->number),
            'enable' => !empty($request->enable) ? intval($request->enable) : 1,
            'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
            'updated_at' => date('Y-m-d H:i:s'),
            'bottom_id' => $id,
            'parent_id' => $parentId,
            'name' => trim($request->name),
            'long_name' => ltrim($longName . '_' . trim($request->name), '_'),
            'parent_ids' => !empty($request->parent_id) ? ltrim($parentIds . ',' . $parentId, ',') : '1',
            'contact_name' => !empty($request->contact_name) ? trim($request->contact_name) : '',
            'contact_phone' => !empty($request->contact_phone) ? trim($request->contact_phone) : '',
            'contact_email' => !empty($request->contact_email) ? trim($request->contact_email) : '',
            'describe' => !empty($request->describe) ? trim($request->describe) : '',
            'province_id' => !empty($request->province_id) ? intval($request->province_id) : 0,
            'city_id' => !empty($request->city_id) ? intval($request->city_id) : 0,
            'contact_address' => !empty($request->contact_address) ? trim($request->contact_address) : '',
        ]);
        PurchaserBusiness::upsert([
            'purchaser_id' => $id,
            'social_code' => !empty($request->social_code) ? trim($request->social_code) : '',
            'legal_person' => !empty($request->legal_person) ? trim($request->legal_person) : '',
            'company_type' => !empty($request->company_type) ? trim($request->company_type) : '',
            'establishment_date' => !empty($request->establishment_date) ? trim($request->establishment_date) : null,
            'business_scope' => !empty($request->business_scope) ? trim($request->business_scope) : null,
            'taxpayer_number' => !empty($request->taxpayer_number) ? trim($request->taxpayer_number) : null,
            'bank' => !empty($request->bank) ? trim($request->bank) : null,
            'bank_account' => !empty($request->bank_account) ? trim($request->bank_account) : null,
            'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
            'updated_at' => date('Y-m-d H:i:s'),
                ], ['purchaser_id'], ['social_code',
            'legal_person',
            'company_type',
            'establishment_date',
            'business_scope',
            'taxpayer_number',
            'bank',
            'bank_account',
        ]);
//        $userId = UserPurchaser::where('purchaser_id', $id)->value('user_id');
//        if (empty($userId)) {
//            $this->insertUser($request, $id);
//        }
        UserPurchaser::where('purchaser_id', $id)->update(['bot_purchaser_id' => $id]);
        return true;
    }

    public function insertUser(Request $request, int $purchaserId) {
        $phoneEmail = trim($request->phone_email);
        if (isEmail($phoneEmail)) {
            $email = $phoneEmail;
        } elseif (is_mobile($phoneEmail)) {
            $phone = $phoneEmail;
        }
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        $userId = User::insertGetId([
                    'user_type' => 'SUPPLIER',
                    'phone' => !empty($phone) ? trim($phone) : '',
                    'username' => !empty($request->realname) ? trim($request->realname) : '',
                    'email' => !empty($email) ? trim($email) : '',
                    'realname' => !empty($request->realname) ? trim($request->realname) : '',
                    'full_pinyin' => $pinyin->permalink(trim($request->realname), ''),
                    'password' => password_hash(trim($request->password), PASSWORD_DEFAULT),
                    'password_flag' => 0,
                    'status' => 1,
                    'gender' => 'SECRECY',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        UserPurchaser::insertGetId([
            'user_id' => $userId,
            'purchaser_id' => $purchaserId,
            'bot_purchaser_id' => $purchaserId,
            'is_manager' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        UserContacts::insertGetId([
            'user_id' => $userId,
            'status' => 1,
            'phone' => !empty($phone) ? trim($phone) : '',
            'email' => !empty($email) ? trim($email) : '',
            'name' => !empty($request->realname) ? trim($request->realname) : '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
//        Mail::to($request->contact_email)
//          ->send(new AccountCreated(['mail' => trim($request->contact_email),
//              'phone' => trim($request->contact_phone)]));
        return true;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function add(Request $request) {
        $admin = Auth::guard('admin')->user();
        $parentId = !empty($request->parent_id) ? intval($request->parent_id) : 1;
        $purchaser = Purchaser::select('id', 'long_name', 'parent_ids', 'bottom_id')
                ->where('id', $parentId)
                ->first();
        $parentIds = !empty($purchaser) ? $purchaser->parent_ids : '';
        $longName = !empty($purchaser) ? $purchaser->long_name : '';
        $purchaserId = Purchaser::insertGetId([
                    'purchaser_type' => 'PURCHASER',
                    'number' => !empty(trim($request->number)) ? trim($request->number) : $this->getPurchaserNo(),
                    'enable' => !empty($request->enable) ? intval($request->enable) : 1,
                    'created_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'parent_id' => $parentId,
                    'name' => trim($request->name),
                    'long_name' => ltrim($longName . '_' . trim($request->name), '_'),
                    'parent_ids' => !empty($parentId) ? ltrim($parentIds . ',' . $parentId, ',') : '1',
                    'contact_name' => !empty($request->contact_name) ? trim($request->contact_name) : '',
                    'contact_phone' => !empty($request->contact_phone) ? trim($request->contact_phone) : '',
                    'contact_email' => !empty($request->contact_email) ? trim($request->contact_email) : '',
                    'describe' => !empty($request->describe) ? trim($request->describe) : '',
                    'province_id' => !empty($request->province_id) ? intval($request->province_id) : 0,
                    'city_id' => !empty($request->city_id) ? intval($request->city_id) : 0,
                    'contact_address' => !empty($request->contact_address) ? trim($request->contact_address) : '',
                    'status' => 'APPROVED',
        ]);
        Purchaser::where('id', $purchaserId)->update(['bottom_id' => $purchaserId]);
        PurchaserBusiness::insert([
            'purchaser_id' => $purchaserId,
            'social_code' => !empty($request->social_code) ? trim($request->social_code) : '',
            'legal_person' => !empty($request->legal_person) ? trim($request->legal_person) : '',
            'company_type' => !empty($request->company_type) ? trim($request->company_type) : '',
            'establishment_date' => !empty($request->establishment_date) ? trim($request->establishment_date) : null,
            'business_scope' => !empty($request->business_scope) ? trim($request->business_scope) : null,
            'taxpayer_number' => !empty($request->taxpayer_number) ? trim($request->taxpayer_number) : null,
            'bank' => !empty($request->bank) ? trim($request->bank) : null,
            'bank_account' => !empty($request->bank_account) ? trim($request->bank_account) : null,
            'created_by' => !empty($admin->user_id) ? $admin->user_id : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
//        $this->insertUser($request, $purchaserId);
        return $purchaserId;
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function enable(Request $request) {
        $admin = Auth::guard('admin')->user();
        if ($request->type === 'ALL') {
            return Purchaser::where('enable', 0)
                            ->where('purchaser_type', 'PURCHASER')
                            ->update([
                                'enable' => 1,
                                'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $ids = $request->ids;
            return Purchaser::whereIn('id', $ids)
                            ->where('purchaser_type', 'PURCHASER')
                            ->update([
                                'enable' => 1,
                                'updated_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function disable(Request $request) {
        $admin = Auth::guard('admin')->user();
        if ($request->type === 'ALL') {
            return Purchaser::where('enable', 1)
                            ->where('purchaser_type', 'PURCHASER')
                            ->update([
                                'enable' => 1,
                                'disabled_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'disabled_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $ids = $request->ids;
            return Purchaser::whereIn('id', $ids)
                            ->where('purchaser_type', 'PURCHASER')
                            ->update([
                                'enable' => 0,
                                'disabled_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                                'disabled_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $ids = $request->ids;
        return Purchaser:: where('purchaser_type', 'PURCHASER')->whereIn('id', $ids)->update(['deleted_flag' => 'Y']);
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->whereIN('p.purchaser_type', ['PURCHASER', 'PLATFORM'])
                ->where('p.deleted_flag', 'N');
        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('p.name', 'like', '%' . $keyword . '%')
                        ->orWhere('p.number', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->purchaser_type)) {
            $query->where('purchaser_type', trim($request->purchaser_type));
        }
        if (!empty($request->country_id)) {
            $countryId = trim($request->country_id);
            $countryIds = explode(',', $countryId);
            $query->whereIn('p.country_id', $countryIds);
        }

        if (!empty($request->parent_id)) {
            $parentId = intval($request->parent_id);
            $query->where(function($q)use($parentId) {
                $q->where('id', $parentId)
                        ->orWhereRaw('FIND_IN_SET(' . $parentId . ',parent_ids) ');
            });
//            $query->whereRaw('FIND_IN_SET(' . $parentId . ',p.parent_ids) ');
        }
        if (!empty($request->status)) {
            $status = $request->status;
            $statusies = is_array($status) ? $status : explode(',', trim($status));
            $query->whereIn('p.status', $statusies);
        }
        if (!empty($request->enable) || $request->enable === '0') {
            $enable = $request->enable;
            $enables = is_array($enable) ? $enable : explode(',', trim($enable));
            foreach ($enables as &$enable) {
                switch ($enable) {
                    case '2':
                        $enable = 0;
                        break;
                    case '1':
                        $enable = 1;
                        break;
                }
            }
            $query->whereIn('p.enable', $enables);
        }
//        else {
////            $query->where('p.enable', 1);
//        }
        if (!empty($request->number)) {
            $query->where('number', 'like', '%' . $request->number . '%');
        }
        if (!empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if (!empty($request->contact_name)) {
            $query->where('contact_name', 'like', '%' . $request->contact_name . '%');
        }
        if (!empty($request->long_name)) {
            $query->where('long_name', 'like', '%' . $request->long_name . '%');
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('p.created_at', $createAts);
        } else if (!empty($request->createtime)) {
            $createtime = trims($request->createtime);
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('p.created_at', $createAts);
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setPurchasers(array &$list, string $field = 'purchaser_id', $fieldKey = 'purchaser_name') {
        if (empty($list)) {
            return;
        }
        $purchaserIds = [];
        foreach ($list as &$val) {
            $val[$fieldKey] = '';
            if (isset($val[$field]) && $val[$field]) {
                $purchaserIds[] = $val[$field];
            }
        }

        if (empty($purchaserIds)) {
            return $list;
        }
        $qurey = $this->model
                ->whereIn('purchaser_type', ['PURCHASER', 'PLATFORM'])
                ->select('id', 'name');
        $qurey->whereIn('id', $purchaserIds);
        $purchaserObjects = $qurey->get();
        if (empty($purchaserObjects)) {
            return $list;
        }
        $purchasers = $purchaserObjects->toArray();
        $purchaserArr = [];
        foreach ($purchasers as $purchaser) {
            $purchaserArr[$purchaser['id']] = $purchaser['name'];
        }
        foreach ($list as &$val) {
            if (isset($val[$field]) && isset($purchaserArr[$val[$field]])) {
                $val[$fieldKey] = $purchaserArr[$val[$field]];
            }
        }
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc 获取行业
     */
    public function setPurchaser(array &$arr, string $field = 'purchaser_id', $fieldKey = 'purchaser_name') {
        if (empty($arr)) {
            return;
        }
        $purchaserId = '';
        if (isset($arr[$field]) && $arr[$field]) {
            $purchaserId = $arr[$field];
        }

        $arr[$fieldKey] = '';
        if (empty($purchaserId)) {
            return $arr;
        }
        $arr[$fieldKey] = $this->model
                ->whereIn('purchaser_type', ['PURCHASER', 'PLATFORM'])
                ->where('id', $purchaserId)
                ->value('name');
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $table = $this->model->getTable();
        $btable = (new PurchaserBusiness)->getTable();
        $query = $this->model
                ->from($table . ' as p')
                ->join($btable . ' as pb', function($join) {
                    $join->on('p.id', '=', 'pb.purchaser_id');
                })
                ->selectRaw('p.id,p.number,p.parent_id,p.long_name,p.enable,p.name,p.contact_name,'
                . 'p.contact_phone,p.contact_email,p.describe,p.province_id,'
                . 'p.city_id,p.contact_address,p.created_by,p.created_at,'
                . 'p.updated_by,p.updated_at,p.deleted_flag,pb.purchaser_id,'
                . 'pb.social_code,pb.legal_person,pb.company_type,'
                . 'pb.establishment_date,pb.business_scope,pb.taxpayer_number,'
                . 'pb.bank,pb.bank_account');
        if ($request->type === 'ALL') {
            $query->where('p.purchaser_type', 'PURCHASER')
                    ->where('p.deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('p.purchaser_type', 'PURCHASER')
                    ->where('p.deleted_flag', 'N')
                    ->whereIn('p.id', $request->ids);
        } else {
            $this->getWhere($query, $request);
        }
        $object = $query->orderBy('created_at', 'DESC')->get();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $this->setPurchasers($data, 'parent_id', 'parent_name');
        (new UserRepo)->setUsers($data, 'created_by', 'created_name');
        (new UserRepo)->setUsers($data, 'updated_by', 'updated_name');
        (new DivisionRepo)->setDivisions($data, 'province_id', 'province');
        (new DivisionRepo)->setDivisions($data, 'city_id', 'city');
        $headName = $this->getHeadName();
        $xlsName = "Purchaser_" . date("YmdHis", time()) . uniqid(); //文件名称
        return $this->downloadExcel($xlsName, $data, $headName);
    }

    private $styleArray = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
        'font' => [
            'name' => 'Arial',
            'bold' => false,
            'italic' => false,
            'size' => 9,
            'underline' => Font::UNDERLINE_NONE,
            'strikethrough' => false,
            'color' => [
                'rgb' => '000000'
            ]
        ],
        'numberFormat' => ['formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '00000000'],
            ],
        ],
    ];

    public function setExcelRow($sheet, $col, $row, $value, $width) {
        $sheet->setCellValue($col . $row, $value);
        $sheet->getStyle($col . $row)->applyFromArray($this->styleArray);
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    /**
     * 导出
     * @param type $request Description
     * @param $name
     * @param array $data
     * @param array $head
     * @return array
     */
    public function downloadExcel($name, $data = [], $head = []) {
        $count = count($head);  //计算表头数量
        $spreadsheet = Excel::newSpreadsheet();
        $styleArray = $this->styleArray;
        $sheet = $spreadsheet->getSpreadsheet()->getActiveSheet();
        $sheet->mergeCells('A1:V1');
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->setCellValue('A1', '采购商');
        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始
            $this->setExcelRow($sheet, strtoupper(chr($i)), 2, $head[$i - 65], 20);
        }
        $row = 3;

        foreach ($data as $item) {
//数字转字母从65开始：
            $this->setExcelRow($sheet, 'A', $row, ' ' . $item['number'], 17);
            $sheet->getStyle('A' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'B', $row, $item['name'], 24);
            $sheet->getStyle('B' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'C', $row, $item['parent_name'], 24);
            $sheet->getStyle('C' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'D', $row, $item['long_name'], 24);
            $sheet->getStyle('D' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'E', $row, $item['contact_name'], 24);
            $sheet->getStyle('E' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'F', $row, ' ' . $item['contact_phone'], 24);
            $sheet->getStyle('F' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'G', $row, ' ' . $item['contact_email'], 24);
            $sheet->getStyle('G' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'H', $row, $item['social_code'], 24);
            $sheet->getStyle('H' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'I', $row, $item['legal_person'], 24);
            $sheet->getStyle('I' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'J', $row, $item['company_type'], 24);
            $sheet->getStyle('J' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'K', $row, $item['establishment_date'], 24);
            $sheet->getStyle('K' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'L', $row, $item['taxpayer_number'], 24);
            $sheet->getStyle('L' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'M', $row, $item['bank'], 24);
            $sheet->getStyle('M' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'N', $row, $item['bank_account'], 24);
            $sheet->getStyle('N' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'O', $row, $item['business_scope'], 24);
            $sheet->getStyle('O' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'P', $row, $item['province'], 24);
            $sheet->getStyle('P' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'Q', $row, $item['city'], 24);
            $sheet->getStyle('Q' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'R', $row, $item['contact_address'], 24);
            $sheet->getStyle('R' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'S', $row, $item['describe'], 24);
            $sheet->getStyle('S' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'T', $row, $item['updated_name'], 24);
            $sheet->getStyle('T' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'U', $row, $item['updated_at'], 24);
            $sheet->getStyle('U' . $row)->applyFromArray($styleArray);
            $this->setExcelRow($sheet, 'V', $row, $item['enable'] == 1 ? '可用' : '禁用', 24);
            $sheet->getStyle('V' . $row)->applyFromArray($styleArray);
            $row++;
        }
        $spreadsheet
                ->getSpreadsheet()
                ->getActiveSheet()
                ->getStyle('A1:V2')
                ->applyFromArray($styleArray);
        $realtive = "/download/" . date("Ymd") . '/';
        $filename = $name . '.xlsx';
        $filedir = base_path() . '/public' . $realtive;
        @mkdir($filedir, 0777, true);
        $filepath = $filedir . $filename;
        $spreadsheet->save($filepath);
        $url = env('APP_URL') . $realtive . $filename;
        return ['file_url' => $url, 'attach_name' => $filename];
    }

    /**
     * 获取headName
     * @param $data
     * @return array
     */
    public function getHeadName() {
        return [
            '编码',
            '名称',
            '父级供应商名称',
            '长名称',
            '联系人',
            '联系电话',
            '邮箱',
            '统一社会信用代码',
            '法定代表人',
            '公司类型',
            '成立日期',
            '纳税人识别号',
            '开户行',
            '银行账户',
            '经营范围',
            '省',
            '市',
            '联系地址',
            '描述',
            '最后更新人',
            '最后更新时间',
            '使用状态',
        ];
    }

    public function getPrentId($parentName) {
        if (empty($parentName)) {
            return 0;
        }
        return $this->model->where('name', $parentName)
                        ->where('purchaser_type', 'PURCHASER')
                        ->where('deleted_flag', 'N')
                        ->value('id');
    }

    /**
     * @desc 处理业务SKU参数
     *
     * @param array $importData 规格属性
     * @return bool
     * @author zhongyg
     * @time 2019-06-14
     */
    public function importItemHandler($importData) {
        array_shift($importData); //去掉第二行数据(excel文件的标题)
        array_shift($importData);
        if (empty($importData)) {
            return ['code' => '-104', 'message' => '没有要导入的数据'];
        }
        $admin = Auth::guard('admin')->user();
        $data = $this->dataTrim($importData);
        $list = [];
        $newNumber = null;
        foreach ($data as $v) {
            $item['number'] = !empty(trim($v[0])) ? trim($v[0]) : $this->getPurchaserNo($newNumber); //执行标准
            $item['name'] = trim($v[1]); //执行标准
            $item['country'] = '中国'; //执行标准
            $item['parent_id'] = $this->getPrentId(trim($v[2])); //质保期
            $item['long_name'] = trim($v[3]); //海关编码
            $item['contact_name'] = trim($v[4]); //监管条件
            $item['contact_phone'] = trim($v[5]); //退税率
            $item['contact_email'] = trim($v[6]); //退税率
            $item['social_code'] = trim($v[7]); //退税率
            $item['legal_person'] = trim($v[8]); //退税率
            $item['company_type'] = trim($v[9]); //退税率
            $item['establishment_date'] = trim($v[10]); //退税率
            $item['taxpayer_number'] = trim($v[11]); //退税率
            $item['bank'] = trim($v[12]); //退税率
            $item['bank_account'] = trim($v[13]); //退税率
            $item['business_scope'] = trim($v[14]); //退税率
            $item['province'] = trim($v[15]); //退税率
            $item['city'] = trim($v[16]); //退税率
            $item['contact_address'] = trim($v[17]); //退税率
            $item['describe'] = trim($v[18]); //退税率
            $item['modifier_id'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素          
            $item['creator_id'] = !empty($admin->user_id) ? $admin->user_id : 0; //申报要素     
            $item['create_time'] = date('Y-m-d H:i:s'); //申报要素          
            $item['modify_time'] = date('Y-m-d H:i:s'); //申报要素     
            $item['enable'] = trim($v[21]) === '可用' ? '1' : 0; //质保期  
            $item['status'] = 'APPROVED';
            $list[] = $item;
        }
        (new CountryRepo)->setCountryIds($list, 'country', 'country_id');
        (new DivisionRepo)->setDivisionIds($list, 'province', 'province_id');
        (new DivisionRepo)->setDivisionIds($list, 'city', 'city_id');
        $errorList = [];
        foreach ($list as $key => $item) {
            $errors = [];
            if (empty(trim($item['social_code']))) {
                $errors[] = '统一社会信用代码不能为空';
            }
            if (empty(trim($item['number']))) {

                $errors[] = '编码不能为空';
            }
            if (empty(trim($item['name']))) {
                $errors[] = '名称不能为空';
            }
            if (empty(trim($item['contact_name']))) {

                $errors[] = '联系人不能为空';
            }
            if (empty(trim($item['contact_phone']))) {
                $errors[] = '联系电话不能为空';
            }
            if (empty(trim($item['contact_email']))) {
                $errors[] = '邮箱不能为空';
            }
            $curId = Purchaser::where('number', trim($item['number']))->value('id');
            $count = !empty($curId) ? Purchaser::whereNot('id', $curId)
                            ->where('name', trim($item['name']))
                            ->count() : Purchaser::where('name', trim($item['name']))->count();
            if (!empty($count)) {
                $errors[] = '名称已存在';
            }
            $countS = !empty($curId) ? PurchaserBusiness::whereNot('purchaser_id', $curId)
                            ->where('social_code', trim($item['social_code']))
                            ->count() : PurchaserBusiness::where('social_code', trim($item['social_code']))->count();
            if (!empty($countS)) {
                $errors[] = '统一社会信用代码已存在';
            }
            $errorList[$key] = $errors;
            if (!empty($errors)) {
                continue;
            }
            $data = [
                'purchaser_type' => 'PURCHASER',
                'number' => trim($item['number']),
                'enable' => !empty($item['enable']) ? intval($item['enable']) : 1,
                'created_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'parent_id' => !empty($item['parent_id']) ? intval($item['parent_id']) : 1,
                'name' => trim($item['name']),
                'long_name' => trim($item['long_name']),
                'contact_name' => trim($item['contact_name']),
                'contact_phone' => trim($item['contact_phone']),
                'contact_email' => trim($item['contact_email']),
                'describe' => trim($item['describe']),
                'province_id' => !empty($item['province_id']) ? intval($item['province_id']) : 0,
                'city_id' => !empty($item['city_id']) ? intval($item['city_id']) : 0,
                'contact_address' => !empty($item['contact_address']) ? trim($item['contact_address']) : '',
            ];
            $purchaserId = $curId;
            $curId ? Purchaser::where('id', $curId)->update($data) : $purchaserId = Purchaser::insertGetId($data);
            $businessData = [
                'purchaser_id' => $purchaserId,
                'social_code' => !empty($item['social_code']) ? trim($item['social_code']) : '',
                'legal_person' => !empty($item['legal_person']) ? trim($item['legal_person']) : '',
                'company_type' => !empty($item['company_type']) ? trim($item['company_type']) : '',
                'establishment_date' => !empty($item['establishment_date']) ? trim($item['establishment_date']) : null,
                'business_scope' => !empty($item['business_scope']) ? trim($item['business_scope']) : null,
                'taxpayer_number' => !empty($item['taxpayer_number']) ? trim($item['taxpayer_number']) : null,
                'bank' => !empty($item['bank']) ? trim($item['bank']) : null,
                'bank_account' => !empty($item['bank_account']) ? trim($item['bank_account']) : null,
                'created_by' => !empty($admin->user_id) ? $admin->user_id : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $curId ? PurchaserBusiness::where('purchaser_id', $purchaserId)->update($businessData) : PurchaserBusiness::insert($businessData);
            $request = new Request();
            $request->merge($item);
//            $this->insertUser($request, $purchaserId);
        }
        return $errorList;
    }

    /**
     * @desc 去掉数据两侧的空格
     *
     * @param mixed $data
     * @return mixed
     * @author liujf
     * @time 2018-02-02
     */
    function dataTrim($data) {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = $this->dataTrim($v);
            }
            return $data;
        }
        if (is_string($data)) {
            return trim($data);
        }
        return $data;
    }

    private function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    public function download2local($tmpSavePath, $remoteFile, $attach_name) {
//设置本地临时保存目录
        $localFullFileName = $tmpSavePath . mb_convert_encoding(urldecode(basename($attach_name)), 'GB2312', 'UTF-8');
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $file = fopen($remoteFile, 'rb', null, $context);
        if ($file) {
            $newf = fopen($localFullFileName, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        return $localFullFileName;
    }

    public function import(Request $request) {
        $remoteFile = $request->file_url;
        $attachName = $request->attach_name;
        $ds = DIRECTORY_SEPARATOR;
        $tmpDir = app()->basePath() . $ds . 'resources' . $ds . 'tmp' . $ds . uniqid() . $ds;
        RecursiveMkdir($tmpDir);
        $localFile = $this->download2local($tmpDir, $remoteFile, $attachName);
        $importData = $this->ready2import($localFile, 0);
        return $this->importItemHandler($importData);
    }

    public function ready2import($localFile, $pIndex = 0) {
//获取文件类型
        $fileType = IOFactory::identify($localFile);
//创建PHPExcel读取对象
        $objReader = IOFactory::createReader($fileType);
//加载文件并读取
        $officeSheet = $objReader->load($localFile);
        $data = $officeSheet->getSheet($pIndex)->toArray();
        return $data;
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getPurchaserNo(&$newNumber = null) {
        $prefix = 'RUI-';
        $qurey = $this->model->selectRaw('*');
        $number = $newNumber ? $newNumber : $qurey
                        ->where('number', 'like', $prefix . '%')
                        ->orderBy('number', 'DESC')
                        ->value('number');
        if (!empty($number)) {
            $date = substr($number, 4, 8);
            $serialSetp = substr($number, 14, 5);
            $step = intval($serialSetp);
            $step ++;
            $newNumber = $this->createSerialNo($step, $prefix, $date);
            return $newNumber;
        }
        $newNumber = $this->createSerialNo(1, $prefix, '');
        return $newNumber;
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2019-03-11
     * @return string $code
     */
    private function createSerialNo($step = 1, $prefix = '', $date = '') {
        $time = date('Ymd');
        if (empty($date) || $date < $time) {
            $step = 1;
        }
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        return$prefix . $time . $pad;
    }

    public function phoneEmail(Request $request) {
        $phoneEmail = $request->post('phone_email');
        $code = randomnum(6);
        $expiredSeconds = config('login.email_expired');
        if ($expiredSeconds > 3600) {
            $expired = ($expiredSeconds / 3600) . '小时';
        } else {
            $expired = ($expiredSeconds / 60) . '分钟';
        }

        $user = ['phone_email' => $phoneEmail, 'code' => $code, 'expired' => $expired];
        $sendAt = date('y-m-d H:i:s');
        if (isEmail($phoneEmail)) {
            $type = 'email';
            $title = 'VerifyMail';
            $response = Mail::to($phoneEmail)->send(new VerifyMail($user));
        } else {
            check(false, '邮箱号不正确');
        }
        Redis::setex($request->post('register_supplier_code_' . $phoneEmail), $expiredSeconds, $code);
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $phoneEmail,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => $sendAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return [];
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function register(Request $request) {
        $purchaserId = Purchaser::insertGetId([
                    'status' => 'DRAFT',
                    'name' => trim($request->name),
                    'created_at' => date('Y-m-d H:i:s'),
                    'number' => $this->getPurchaserNo(),
                    'enable' => 1,
        ]);
        PurchaserBusiness::insert([
            'purchaser_id' => $purchaserId,
            'created_by' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->insertUser($request, $purchaserId);
        return $purchaserId;
    }

}
