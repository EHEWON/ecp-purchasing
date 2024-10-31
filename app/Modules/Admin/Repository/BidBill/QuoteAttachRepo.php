<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use App\Common\Models\BidBill\QuoteAttach;
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteAttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new QuoteAttach();
        parent::__construct($this->model);
    }

    public function getList(int $bidBillQuoteId) {
        if (empty($bidBillQuoteId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('bid_bill_quote_id', $bidBillQuoteId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
        }
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        return $list;
    }

    public function updateData(int $bidBillId, Request $request) {
        QuoteAttach::where('bid_bill_quote_id', $bidBillId)->delete();
        $attach = $this->getAttachs($bidBillId, $request);
        if (!empty($attach)) {
            QuoteAttach::insert($attach);
        }
    }

    public function getAttachs(int $bidBillQuoteId, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->quote_attach)) {
            $attach = $request->quote_attach;

            if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                return [];
            }
            $attachList[] = [
                'bid_bill_quote_id' => $bidBillQuoteId,
                'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                'created_by' => $admin->user_id,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        return $attachList;
    }

    /**
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function setQuoteAttachs(array &$list, $filed = 'id') {
        if (empty($list)) {
            return;
        }
        $bidBillQuoteIds = [];
        foreach ($list as &$val) {
            $val['attach_name'] = '';
            $val['attach_url'] = '';
            $val['attach_remark'] = '';
            $bidBillQuoteIds[] = $val[$filed];
        }
        if (empty($bidBillQuoteIds)) {
            return $list;
        }
        $fields = 'bid_bill_quote_id,attach_name,attach_url,remarks';
        $qurey = $this->model->selectRaw($fields);
        $qurey->whereIn('bid_bill_quote_id', $bidBillQuoteIds);
        $attachObjects = $qurey->get();
        if (empty($attachObjects)) {
            return $list;
        }
        $attachs = $attachObjects->toArray();
        $attachArr = [];
        foreach ($attachs as $attach) {
            $attachArr[$attach['bid_bill_quote_id']] = $attach;
        }

        foreach ($list as &$val) {
            if (empty($attachArr[$val[$filed]])) {
                continue;
            }
            $attach = $attachArr[$val[$filed]];
            $val['attach_name'] = $attach['attach_name'];
            $val['attach_url'] = $attach['attach_url'];
            $val['attach_remark'] = $attach['remarks'];
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
    public function setQuoteAttach(array &$data, string $field = 'id') {
        if (empty($data)) {
            return;
        }

        $data['attach_name'] = '';
        $data['attach_url'] = '';
        $data['attach_remark'] = '';
        $fields = 'bid_bill_quote_id,attach_name,attach_url,remarks';
        $qurey = $this->model->selectRaw($fields);
        $qurey->where('bid_bill_quote_id', $data[$field]);
        $attachObject = $qurey->first();
        if (empty($attachObject)) {
            return $data;
        }
        $attach = $attachObject->toArray();
        $data['attach_name'] = $attach['attach_name'];
        $data['attach_url'] = $attach['attach_url'];
        $data['attach_remark'] = $attach['remarks'];
        return $data;
    }

}
