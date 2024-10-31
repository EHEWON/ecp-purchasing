<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Supplier;
use Illuminate\Http\Request;
use App\Common\Models\{
    UserSupplier,
    User
};

class SupplierRegisterRepo extends Repository {

    protected $model;
    protected $sorts = [
        'realname',
        'phone',
        'name',
        'enable',
        'registered_at',
        'created_at',
    ];

    public function __construct() {
        $this->model = new Supplier();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query, Request $request) {
        /**
         * 排序
         */
        $sort = !empty($request->sort) && in_array(strtolower(trim($request->sort)), $this->sorts) ? trim($request->sort) : 'created_at';
        $order = !empty($request->order) && in_array(strtolower(trim($request->order)), ['desc', 'asc']) ? trim($request->order) : 'DESC';
        switch ($sort) {
            case 'name':
                $query->orderBy('s.name', $order);
                break;
            case 'registered_at':
                $query->orderBy('s.registered_at', $order);
                break;
            case 'realname':
                $query->orderBy('u.realname', $order);
                break;
            case 'phone':
                $query->orderBy('u.phone', $order);
                break;
            default:
                $query->orderBy('s.created_at', $order);
                break;
        }
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = $this->model->getTable();
        $user = (new User)->getTable();
        $query = $this->model
                ->selectRaw('u.realname,u.phone,u.email,s.status,s.supplier_no,s.registered_at,s.id,s.name')
                ->from($supplier . ' as s')
                ->leftJoin($userSupplier . ' as us', function($join) {
                    $join->on('s.id', 'us.supplier_id')
                    ->where('us.is_manager', 1)
                    ->where('us.deleted_flag', 'N');
                })
                ->leftJoin($user . ' as u', function($join) {
            $join->on('u.user_id', 'us.user_id')
            ->where('u.deleted_flag', 'N');
        });
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
        foreach ($data as &$item) {
            if ($item['status'] !== 'APPROVED') {
                $item['supplier_no'] = '';
            }
            $item['phone_email'] = !empty($item['phone']) && !empty($item['email']) ? $item['phone'] . '/' . $item['email'] :
                    (!empty($item['phone']) ? $item['phone'] : $item['email']);
            unset($item['phone'], $item['email']);
        }
        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('s.deleted_flag', 'N');
        $query->where(function($q) {
            $q->whereIn('s.status', ['APPROVING', 'APPROVED', 'INVALID', 'REVIEW'])
                    ->whereIn('s.source', ['BOSS', 'PURCHASER'])
                    ->orWhere(function($q1) {
                        $q1->whereIn('s.status', ['APPROVING', 'APPROVED', 'INVALID'])
                        ->where('s.source', 'REGISTER');
                    });
        });

        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q)use($keyword) {
                $q->where('u.realname', 'like', '%' . $keyword . '%')
                        ->orWhere('s.name', 'like', '%' . $keyword . '%')
                        ->orWhere('u.email', 'like', '%' . $keyword . '%')
                        ->orWhere('u.phone', 'like', '%' . $keyword . '%');
            });
        }
        if (!empty($request->realname)) {
            $realname = trim($request->realname);
            $query->where('u.realname', 'like', '%' . $realname . '%');
        }
        if (!empty($request->name)) {
            $realname = trim($request->name);
            $query->where('s.name', 'like', '%' . $realname . '%');
        }
        if (!empty($request->supplier_no)) {
            $supplierNo = trim($request->supplier_no);
            $query->where('s.supplier_no', 'like', '%' . $supplierNo . '%');
        }
        if (!empty($request->has_no)) {
            $hasNo = $request->has_no === 'Y' ? 'Y' : 'N';
            $hasNo === 'Y' ? $query->whereNotNull('s.supplier_no')->where('s.supplier_no', '<>', '') :
                            $query->where(function($q) {
                                $q->whereNull('s.supplier_no')
                                        ->orWhere('s.supplier_no', '');
                            });
        }
        if (!empty($request->phone_email)) {
            $phoneEmail = trim($request->phone_email);
            $query->where(function ($q)use($phoneEmail) {
                $q->where('u.phone', 'like', '%' . $phoneEmail . '%')
                        ->orWhere('u.email', 'like', '%' . $phoneEmail . '%');
            });
        }
        if (!empty($request->registertype)) {
            $createAts = $this->getTimeByType($request->registertype);
            $query->whereBetween('s.registered_at', $createAts);
        } elseif (!empty($request->registertime)) {
            $createtime = $request->registertime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('s.registered_at', $createAts);
        }

        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $query->whereBetween('s.registered_at', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('s.registered_at', $createAts);
        }
    }

}
