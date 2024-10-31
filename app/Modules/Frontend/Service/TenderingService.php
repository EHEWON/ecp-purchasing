<?php

namespace App\Modules\Frontend\Service;

use App\Common\Contracts\Service;
use Illuminate\Http\Request;
use App\Modules\Frontend\Repository\TenderingRepo;

class TenderingService extends Service {

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
        return (new TenderingRepo())->getList($request);
    }

    public function info(int $id) {
        $data = (new TenderingRepo())->noticeinfo($id);
        echo view('tpl.tendering_detail', $data);
        die;
    }

}
