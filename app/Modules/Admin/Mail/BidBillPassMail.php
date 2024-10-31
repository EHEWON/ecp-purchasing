<?php

namespace App\Modules\Admin\Mail;

use Illuminate\{
    Bus\Queueable,
    Mail\Mailable,
    Queue\SerializesModels
};

class BidBillPassMail extends Mailable {

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
        $subject = '恭喜你，【' . $this->user['title'] . '】的项目，您已中标，请尽快登录系统查看。';
        $this->subject($subject);
        return $this->view('mail.BidBillPass');
    }

}
