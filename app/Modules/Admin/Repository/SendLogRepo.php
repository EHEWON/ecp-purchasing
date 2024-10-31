<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\SendLog;
use Illuminate\Contracts\Bus\Dispatcher;
use App\Jobs\SendMailJob;

class SendLogRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SendLog();
        parent::__construct($this->model);
    }

    public function supplierChange($emails, $name, $supplierId) {
        app(Dispatcher::class)->dispatch
                (new SendMailJob([
            'emails' => $emails,
            'supplier_id' => $supplierId,
            'name' => $name,], 'SUPPLIERCHANGE'));
    }

    public function supplierSubmit($emails, $name, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'emails' => $emails,
            'supplier_id' => $supplierId,
            'name' => $name,], 'SUPPLIERSUBMIT'));
    }

    public function supplierPass($email, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
                ], 'SUPPLIERPASS'));
    }

    public function supplierRefuse($email, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
                ], 'SUPPLIERREFUSE'));
    }

    public function supplierFreezePass($email, $supplierName, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
            'supplier_name' => $supplierName,
                ], 'SUPPLIERFREEZEPASS'));
    }

    public function CompareAudit($email, $purchaserName, $compareId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'purchaserName' => $purchaserName,
            'compareId' => $compareId,
                ], 'COMPAREAUDIT'));
    }

    public function ComparePass($email, $purchaserName, $data, $inquiryId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'purchaserName' => $purchaserName,
            'data' => $data,
            'inquiry_id' => $inquiryId
                ], 'COMPAREPASS'));
    }

    public function CompareRefuse($email, $purchaserName, $inquiryId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'purchaserName' => $purchaserName,
            'inquiry_id' => $inquiryId
                ], 'COMPAREREFUSE'));
    }

    public function supplierUnFreezePass($email, $supplierName, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
            'supplier_name' => $supplierName,
                ], 'SUPPLIERUNFREEZEPASS'));
    }

    public function supplierUnFreezeRefuse($email, $supplierName, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
            'supplier_name' => $supplierName,
                ], 'SUPPLIERUNFREEZEREFUSE'));
    }

    public function supplierChangePass($email, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
                ], 'SUPPLIERCHANGEPASS'));
    }

    public function supplierChangeRefuse($email, $supplierId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'supplier_id' => $supplierId,
                ], 'SUPPLIERCHANGEREFUSE'));
    }

    public function supplierQuote($email, $name, $inquiyrTitle, $inquiryId) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'name' => $name,
            'inquiry_id' => $inquiryId,
            'inquiyrTitle' => $inquiyrTitle
                ], 'SUPPLIERQUOTE'));
    }

    public function BidBillPass($email, $title, $id) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'title' => $title,
            'id' => $id
                ], 'BIDBILLPASS'));
    }

    public function BidBillRefuse($email, $title, $id) {
        app(Dispatcher::class)->dispatch(new SendMailJob([
            'email' => $email,
            'title' => $title,
            'id' => $id
                ], 'BIDBILLREFUSE'));
    }

}
