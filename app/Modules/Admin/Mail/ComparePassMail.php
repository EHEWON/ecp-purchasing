<?php

namespace App\Modules\Admin\Mail;

use Illuminate\{
    Bus\Queueable,
    Mail\Mailable,
    Queue\SerializesModels
};

class ComparePassMail extends Mailable {

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
        $subject = '恭喜您，【' . $this->user['purchaserName'] . '】的询价项目，您已中标，请尽快登录系统查看。';
        $this->subject($subject);
        return $this->view('mail.ComparePass');
    }

}
