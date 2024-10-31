<?php

namespace App\Modules\Admin\Mail;

use Illuminate\{
    Bus\Queueable,
    Mail\Mailable,
    Queue\SerializesModels
};

class SupplierAuditPassMail extends Mailable {

    use Queueable,
        SerializesModels;

    public $user;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct($user) {
        $this->user = $user;
    }

    /**
     * 构建邮件内容
     * @return text
     */
    public function build() {
        $subject = '【' . env('APP_NAME') . '】您提交的企业认证信息已审核通过';
        $this->subject($subject);
        return $this->view('mail.supplierAuditPass');
    }

}
