<?php

namespace App\Modules\Admin\Mail;

use Illuminate\{
    Bus\Queueable,
    Mail\Mailable,
    Queue\SerializesModels
};

class BidBillRefuseMail extends Mailable {

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
        $subject = '很遗憾，【' . $this->user['title'] . '】未中标，请再接再厉';
        $this->subject($subject);
        return $this->view('mail.BidBillRefuse');
    }

}
