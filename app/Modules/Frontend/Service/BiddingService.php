<?php

namespace App\Modules\Frontend\Service;

use App\Common\Contracts\Service;
use Illuminate\Http\Request;
use App\Modules\Frontend\Repository\BiddingRepo;

class BiddingService extends Service {

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

    public function index(Request $request) {
        return (new BiddingRepo())->getList($request);
    }

    public function info(int $id) {
        $data = (new BiddingRepo())->noticeinfo($id);
        echo view('tpl.bidding_detail', $data);
        die;
    }

}
