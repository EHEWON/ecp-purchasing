<?php

namespace App\Modules\Frontend\Service;

use App\Common\Contracts\Service,
    App\Common\Helpers\Page;
use App\Modules\Frontend\Repository\{
    InquiryRepo,
    BiddingRepo,
    TenderingRepo,
    IndexRepo
};
use Illuminate\Support\Facades\Redis,
    Illuminate\Support\Facades\Request as FRequest,
    Illuminate\Http\Request;

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
    }

    public function index() {
        $inquiryRepo = new InquiryRepo();
        $biddingRepo = new BiddingRepo();
        $tenderingRepo = new TenderingRepo();
        $indexRepo = new IndexRepo();
        $request = new Request();
        $token = str_replace('Bearer ', '', FRequest::cookie('token'));
        if (!empty($token) && Redis::command('exists', ['user_' . $token])) {
            $admin = json_decode(Redis::get('user_' . $token), true);
        }
        echo view('tpl.index', [
            'inquiry' => $inquiryRepo->getList($request),
            'bidding' => $biddingRepo->getList($request),
            'tendering' => $tenderingRepo->getList($request),
            'last_quote' => $indexRepo->lastQuote(),
            'last_settled' => $indexRepo->lastSettled(),
            'admin' => !empty($admin) ? $admin : [],
        ]);
        die;
    }

    public function getList(Request $request) {
        $pageSize = !empty($request->pagesize) ?
                $request->pagesize : 10;
        $request->merge(['pagesize' => $pageSize]);
        $data = (new IndexRepo())->getList($request);
        if (isset($data['total'])) {
            $page = new Page($data['total'], $pageSize, [], $pageSize, 10);
        } else {
            $page = new Page(0, $pageSize, [], $pageSize, 10);
        }

        if (!empty($data['biztype_count'])) {
            foreach ($data['biztype_count'] as $key => $item) {
                switch (strtolower($item['biztype'])) {
                    case '1': $data['biztype_count'][$key]['biztype_name'] = '询价信息';
                        break;
                    case '2': $data['biztype_count'][$key]['biztype_name'] = '招标信息';
                        break;
                    case '3': $data['biztype_count'][$key]['biztype_name'] = '竞价信息';
                        break;
                    case 'b': $data['biztype_count'][$key]['biztype_name'] = '竞价结果';
                        break;
                    case '5': $data['biztype_count'][$key]['biztype_name'] = '询价结果';
                        break;
                    case 'a': $data['biztype_count'][$key]['biztype_name'] = '定标公告';
                        break;
                }
            }
        }
        $data['pager'] = $page->show(NULL, [], 'getResult', true, [10, 20, 30, 40]);
        $data['page'] = !empty($request->page) ? intval($request->page) : 1;
        $data['total_pager'] = !empty($data['total']) ? ceil($data['total'] / $pageSize) : 1;
        $data['pagesize'] = $pageSize;
        if ($request->ajax()) {
            $content = view('tpl.list_ajax', $data)->toHtml();
            return $content;
        }
        echo view('tpl.list', $data);
        die;
    }

}
