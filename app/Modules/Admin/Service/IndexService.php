<?php

namespace App\Modules\Admin\Service;

use App\Common\Contracts\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\Admin\Repository\{
    MessageRepo,
    Inquiry\InquiryRepo,
    BidBill\BidBillRepo,
    Supplier\BidBillRepo AS SupplierBidBillRepo,
    Project\ProjectRepo,
    Supplier\ProjectRepo AS SupplierProjectRepo,
    SupplierBaseRepo,
    SupplierAuditRepo,
    Supplier\QuoteRepo AS SupplierQuoteRepo,
    Supplier\InquiryRepo AS SupplierInquiryRepo,
    Supplier\MessageRepo AS SupplierMessageRepo
};

class IndexService extends Service {

    protected $guard = 'admin';
    public $middleware = [];
    public $beforeEvent = [];
    public $afterEvent = [];
    protected $admin = null;

    public function getRules() {
        return [
        ];
    }

    public function getMessages() {
        return [
        ];
    }

    protected $model;

    public function __construct() {
        parent::__construct();
        $this->admin = Auth::guard('admin')->user();
    }

    /**
     * 人员类型列表
     * @param Request $request
     */
    public function statistics() {
        $request = new Request();

        switch ($this->admin->user_type) {
            case 'SUPPLIER':
                $data = [
                    'quote_total' => 0,
                    'project_total' => 0,
                    'bidbill_total' => 0,
                    'adopt_total' => 0,
                ];
                $data['quote_total'] = (new SupplierQuoteRepo)->getTotal($request);
                $data['bidbill_total'] = (new SupplierBidBillRepo)->getTotal($request);
                $data['project_total'] = (new SupplierProjectRepo)->getTotal($request);
                $data['adopt_total'] = (new SupplierQuoteRepo)->getAdoptTotal($request);
                return $data;
            default:
                $data = [
                    'inquiry_total' => 0,
                    'bidbill_total' => 0,
                    'project_total' => 0,
                    'supplier_total' => 0,
                ];
                $data['inquiry_total'] = (new InquiryRepo)->getTotal($request);
                $data['bidbill_total'] = (new BidBillRepo)->getTotal($request);
                $data['project_total'] = (new ProjectRepo)->getTotal($request);
                $data['supplier_total'] = (new SupplierBaseRepo)->getTotal($request);
                return $data;
        }
    }

    /**
     * 人员类型信息
     * @return
     */
    public function todo() {
        switch ($this->admin->user_type) {
            case 'SUPPLIER':
                $data = [
                    'to_be_quoted' => 0, //待报价的询单
                    'quote_handel' => 0, //待处理的报价
                    'enterprise_count' => 0, //完善企业信息
                    'message_handel' => 0, //待处理的消息
                    'bid_bill_quote' => 0, //待报名竞价
                    'bid_bill_pay' => 0, //待缴费竞价
                    'bid_bill_handeling' => 0, //竞价中的竞价
                    'project_handeling' => 0, //待报名招标
                    'project_pay' => 0, //待缴费招标
                    'project_bid' => 0, //待投标招标
                ];
                $supplierTodo = (new SupplierAuditRepo)->todoCount();
                $request = new Request();
                $data['enterprise_count'] = !empty($supplierTodo['enterprise_count']) ? $supplierTodo['enterprise_count'] : 0;
                $request->merge(['read_flag' => 'N']);
                $data['message_handel'] = (new SupplierMessageRepo)->getTotal($request);
                $data['to_be_quoted'] = (new SupplierInquiryRepo)->toBeQuoted();
                $data['quote_handel'] = (new SupplierQuoteRepo)->todoQuoted();
                $data['bid_bill_quote'] = (new SupplierBidBillRepo)->todoQuote($request);
                $data['bid_bill_pay'] = (new SupplierBidBillRepo)->todoPay($request);
                $data['bid_bill_handeling'] = (new SupplierBidBillRepo)->todoHandeling($request);
                $data['project_handeling'] = (new SupplierProjectRepo)->todoHandeling($request);
                $data['project_pay'] = (new SupplierProjectRepo)->todoPay($request);
                $data['project_bid'] = (new SupplierProjectRepo)->todoBid($request);
                return $data;
            default:
                $data = [
                    'supplier_review' => 0, //待审核的供应商
                    'inquiry_handel' => 0, //待处理的询单
                    'compare_handel' => 0, //待比价的询单
                    'message_handel' => 0, //待处理的竞价
                    'bid_bill_handel' => 0, //待启动的竞价
                    'bid_bill_start' => 0, //待定标的竞价
                    'bid_bill_decision' => 0, //待处理的招标
                    'project_handel' => 0, //待处理的消息
                    'project_decision' => 0, //待定标的招标
                ];
                $mrequest = new Request();
                $mrequest->merge(['read_flag' => 'N']);
                $data['message_handel'] = (new MessageRepo)->getTotal($mrequest);
                $data['supplier_review'] = (new SupplierBaseRepo)->pendingTotal();
                $data['inquiry_handel'] = (new InquiryRepo())->todo();
                $data['compare_handel'] = (new InquiryRepo)->compareTodo();
                $data['bid_bill_handel'] = (new BidBillRepo)->todoTotal();
                $data['bid_bill_start'] = (new BidBillRepo)->startTotal();
                $data['bid_bill_decision'] = (new BidBillRepo)->decisionTotal();
                $data['project_handel'] = (new ProjectRepo)->todoTotal();
                $data['project_decision'] = (new \App\Modules\Admin\Repository\Project\ProjectDecisionRepo)->decisionTotal();
                return $data;
        }
    }

    /**
     * 询单统计
     * @param Request $nrequest
     */
    public function inquiryStatistics(Request $nrequest) {
        $data = [];
        $request = new Request();
        $inquiryRepo = new InquiryRepo;
        $request->merge(['createtype' => 'this_week', 'bill_status' => 'C']);
        $data['this_week'] = $inquiryRepo->getTotal($request);
        $request->merge(['createtype' => 'this_month', 'bill_status' => 'C']);
        $data['this_month'] = $inquiryRepo->getTotal($request);
        $request->merge(['createtype' => 'last_month', 'bill_status' => 'C']);
        $data['last_month'] = $inquiryRepo->getTotal($request);
        $request->merge(['createtype' => 'last_week', 'bill_status' => 'C']);
        $data['last_week'] = $inquiryRepo->getTotal($request);
        $data['this_week_qoq'] = $data['last_week'] > 0 ? round(($data['this_week'] - $data['last_week']) / $data['last_week'] * 100, 1) : '--';
        if ($data['this_week_qoq'] !== '--' && $data['this_week_qoq'] > 0) {
            $data['this_week_qoq_status'] = 'up';
            $data['this_week_qoq'] = abs($data['this_week_qoq']) . '%';
        } elseif ($data['this_week_qoq'] !== '--' && $data['this_week_qoq'] < 0) {
            $data['this_week_qoq_status'] = 'down';
            $data['this_week_qoq'] = abs($data['this_week_qoq']) . '%';
        } else {
            $data['this_week_qoq_status'] = '';
        }
        $data['this_month_qoq'] = $data['last_month'] > 0 ? round(($data['this_month'] - $data['last_month']) / $data['last_month'] * 100, 1) : '--';
        if ($data['this_month_qoq'] !== '--' && $data['this_month_qoq'] > 0) {
            $data['this_month_qoq_status'] = 'up';
            $data['this_month_qoq'] = abs($data['this_month_qoq']) . '%';
        } elseif ($data['this_month_qoq'] !== '--' && $data['this_month_qoq'] < 0) {
            $data['this_month_qoq_status'] = 'down';
            $data['this_month_qoq'] = abs($data['this_month_qoq']) . '%';
        } else {
            $data['this_month_qoq_status'] = '';
        }
        if (empty($nrequest->createtype) && empty($nrequest->createtime)) {
            $nrequest->merge(['createtype' => 'past_week', 'bill_status' => 'C']);
        } else {
            $nrequest->merge(['bill_status' => 'C']);
        }
        $data['inquiry_counts'] = $inquiryRepo->getTotalByDate($nrequest);
        return $data;
    }

    /**
     * 报价统计
     * @param Request $nrequest
     */
    public function quoteStatistics(Request $nrequest) {
        $data = [];
        $request = new Request();
        $quoteRepo = new SupplierQuoteRepo;
        $request->merge(['createtype' => 'this_week', 'bill_status' => 'C']);
        $data['this_week'] = $quoteRepo->getTotal($request);
        $request->merge(['createtype' => 'this_month', 'bill_status' => 'C']);
        $data['this_month'] = $quoteRepo->getTotal($request);
        $request->merge(['createtype' => 'last_month', 'bill_status' => 'C']);
        $data['last_month'] = $quoteRepo->getTotal($request);
        $request->merge(['createtype' => 'last_week', 'bill_status' => 'C']);
        $data['last_week'] = $quoteRepo->getTotal($request);
        $data['this_week_qoq'] = $data['last_week'] > 0 ? round(($data['this_week'] - $data['last_week']) / $data['last_week'] * 100, 1) : '--';
        if ($data['this_week_qoq'] !== '--' && $data['this_week_qoq'] > 0) {
            $data['this_week_qoq_status'] = 'up';
            $data['this_week_qoq'] = abs($data['this_week_qoq']) . '%';
        } elseif ($data['this_week_qoq'] !== '--' && $data['this_week_qoq'] < 0) {
            $data['this_week_qoq_status'] = 'down';
            $data['this_week_qoq'] = abs($data['this_week_qoq']) . '%';
        } else {
            $data['this_week_qoq_status'] = '';
        }
        $data['this_month_qoq'] = $data['last_month'] > 0 ? round(($data['this_month'] - $data['last_month']) / $data['last_month'] * 100, 1) . '' : '--';
        if ($data['this_month_qoq'] !== '--' && $data['this_month_qoq'] > 0) {
            $data['this_month_qoq_status'] = 'up';
            $data['this_month_qoq'] = abs($data['this_month_qoq']) . '%';
        } elseif ($data['this_month_qoq'] !== '--' && $data['this_month_qoq'] < 0) {
            $data['this_month_qoq_status'] = 'down';
            $data['this_month_qoq'] = abs($data['this_month_qoq']) . '%';
        } else {
            $data['this_month_qoq_status'] = '';
        }
        if (empty($nrequest->createtype) && empty($nrequest->createtime)) {
            $nrequest->merge(['createtype' => 'past_week', 'bill_status' => 'C']);
        } else {
            $nrequest->merge(['bill_status' => 'C']);
        }

        $data['quote_counts'] = $quoteRepo->getTotalByDate($nrequest);
        return $data;
    }

}
