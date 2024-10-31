<?php

namespace App\Modules\Admin\Mail;

use Illuminate\{
    Bus\Queueable,
    Mail\Mailable,
    Queue\SerializesModels
};

class QuoteMessageMail extends Mailable {

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
        $subject = '【' . $this->user['supplierName'] . '】已对【' . $this->user['inquiry_title'] . '】进行报价。';
        $this->subject($subject);
        return $this->view('mail.QuoteMessage');
    }

}
