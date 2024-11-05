<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\Message;
use App\Common\Models\MessageReceiver;
use App\Modules\Admin\Repository\SupplierBaseRepo;
use App\Modules\Admin\Repository\PurchaserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageRepo extends Repository {

    protected $model;
    protected $sorts = [
    ];

    public function __construct() {
        $this->model = new Message();
        parent::__construct($this->model);
    }

    protected function getOrder(&$query) {
        /**
         * 排序
         */
        $query->orderBy('m.created_at', 'DESC');
    }

    public function notReadCount(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $messageTable = $this->model->getTable();
        $receiverTable = (new MessageReceiver)->getTable();
        $query = $this->model
                ->selectRaw('m.receiver_type,m.sender_id,m.id,m.created_at,m.content_url,'
                        . 'r.supplier_id,m.message_title,m.message,r.read_flag,m.message_type,r.org_id')
                ->from($messageTable . ' as m')
                ->join($receiverTable . ' as r', function($join)use($userId) {
            $join->on('m.id', '=', 'r.message_id')
            ->where('r.receiver_id', $userId);
        });
        $query->where('m.deleted_flag', 'N');
        $query->whereIn('m.receiver_type', ['PLATFORM', 'PURCHASER']);
        $query->where('r.read_flag', 'N');
        $query->where('r.deleted_flag', 'N');
        $total = $query->count();
        $list['total'] = $total;
        return $list;
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function getList(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $messageTable = $this->model->getTable();
        $receiverTable = (new MessageReceiver)->getTable();
        $query = $this->model
                ->selectRaw('m.receiver_type,m.sender_id,m.id,m.created_at,m.content_url,'
                        . 'r.supplier_id,m.message_title,m.message,r.read_flag,m.message_type,r.org_id')
                ->from($messageTable . ' as m')
                ->join($receiverTable . ' as r', function($join)use($userId) {
            $join->on('m.id', '=', 'r.message_id')
            ->where('r.receiver_id', $userId);
        });
        $this->getWhere($query, $request);
        $clone = $query->clone();
        $total = $clone->count();
        $this->getPage($query, $request);
        $this->getOrder($query);
        $object = $query
                ->groupBy('m.id')
                ->get();
        if (empty($object)) {
            return ['data' => [], 'total' => $total];
        }
        $data = $object->toArray();
        foreach ($data as &$item) {
            $item['supplier_id'] = (string) $item['supplier_id'];
            $item['message_type_name'] = $this->getMessageTypeText($item['message_type']);
        }
        (new SupplierBaseRepo)->setSuppliers($data, 'supplier_id', 'supplier_name');

        $list = [];
        $list['total'] = $total;
        $list['data'] = $data;
        return $list;
    }

    public function getTotal(Request $request) {
        $admin = Auth::guard('admin')->user();
        $userId = $admin->user_id;
        $messageTable = $this->model->getTable();
        $receiverTable = (new MessageReceiver)->getTable();
        $query = $this->model
                ->selectRaw('m.receiver_type,m.sender_id,m.id,m.created_at,m.content_url,'
                        . 'r.supplier_id,m.message_title,m.message,r.read_flag,m.message_type,r.org_id')
                ->from($messageTable . ' as m')
                ->join($receiverTable . ' as r', function($join)use($userId) {
            $join->on('m.id', '=', 'r.message_id')
            ->where('r.receiver_id', $userId);
        });
        $this->getWhere($query, $request);
        return $query->count();
    }

    /**
     * @param Request $request
     * @param string $filed
     * @return array
     */
    public function info($id) {
        $messageTable = $this->model->getTable();
        $receiverTable = (new MessageReceiver)->getTable();
        $query = $this->model
                ->selectRaw('m.id,m.created_at,m.content_url,m.sender_id,'
                        . 'm.supplier_id,m.message_title,m.message,r.read_flag,m.sender_id,m.message_type')
                ->from($messageTable . ' as m')
                ->join($receiverTable . ' as r', function($join) {
                    $join->on('m.id', '=', 'r.message_id');
                })
                ->where('m.id', $id);
        $object = $query->first();
        if (empty($object)) {
            return [];
        }
        $data = $object->toArray();
        $data['message_type_name'] = $this->getMessageTypeText($data['message_type']);
        (new PurchaserRepo)->setPurchaser($data, 'sender_id', 'sender_name');
        $data['supplier_id'] = (string) $data['supplier_id'];
        (new SupplierBaseRepo)->setOrg($data, 'supplier_id', 'supplier_name');
        if ($data['read_flag'] === 'N') {
            $admin = Auth::guard('admin')->user();
            $userId = $admin->user_id;
            MessageReceiver::where('message_id', $id)
                    ->where('sender_id', $userId)
                    ->update([
                        'read_flag' => 'Y',
                        'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $data;
    }

    /**
     * @param $query
     * @param Request $request
     * @param bool $statusFlag
     */
    protected function getWhere(&$query, Request $request) {
        $query->where('m.deleted_flag', 'N');
        $query->where('r.deleted_flag', 'N');
        $query->whereIn('m.receiver_type', ['PLATFORM', 'PURCHASER']);
        if (!empty($request->read_flag)) {
            $readFlag = trim($request->read_flag);
            $query->where('r.read_flag', $readFlag == 'N' ? 'N' : 'Y');
        }

        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where('m.message_title', 'like', '%' . $keyword . '%');
        }
        if (!empty($request->createtype)) {
            $createAts = $this->getTimeByType($request->createtype);
            $createAts[1] = date('Y-m-d 23:59:59');
            $query->whereBetween('m.created_at', $createAts);
        } elseif (!empty($request->createtime)) {
            $createtime = $request->createtime;
            $createAts = is_array($createtime) ? $createtime : explode(',', $createtime);
            !empty($createAts[1]) ? $createAts[1] = date('Y-m-d 23:59:59', strtotime($createAts[1])) : $createAts[1] = date('Y-m-d H:i:s');
            $query->whereBetween('m.created_at', $createAts);
        }
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function read(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return MessageReceiver::whereIn('message_id', $ids)
                        ->where('receiver_id', $admin->user_id)
                        ->update([
                            'read_flag' => 'Y',
                            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function deleteData(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return MessageReceiver::whereIn('message_id', $ids)
                        ->where('receiver_id', $admin->user_id)
                        ->update(['deleted_flag' => 'Y']);
    }

    /**
     * @param Array $ids
     * @param string $enable
     * @return array
     */
    public function unread(Request $request) {
        $admin = Auth::guard('admin')->user();
        $ids = $request->ids;
        return MessageReceiver::whereIn('message_id', $ids)
                        ->where('receiver_id', $admin->user_id)
                        ->update([
                            'read_flag' => 'N',
                            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getMessageTypeText($messageType) {
        switch (strtoupper($messageType)) {
            case 'SYSTEM':
                return '系统发送';
            case 'PURCHASER':
                return '采购商发送';
            case 'SUPPLIER':
                return '普通纸质发票';
        }
    }

}
