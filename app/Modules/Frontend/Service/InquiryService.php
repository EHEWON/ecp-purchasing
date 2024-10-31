<?php

namespace App\Modules\Frontend\Service;

use App\Common\Contracts\Service;
use Illuminate\Http\Request;
use App\Modules\Frontend\Repository\InquiryRepo;

class InquiryService extends Service {

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
        return (new InquiryRepo())->getList($request);
    }

    public function info(int $id) {
        $data = (new InquiryRepo())->noticeinfo($id);
        echo view('tpl.inquiry_detail', $data);
        die;
    }

}
