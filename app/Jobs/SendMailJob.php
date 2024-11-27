<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Jobs;

use Illuminate\Mail\Message AS MailMessage;
use Illuminate\Support\Facades\Mail;
use App\Common\Models\{
    User,
    Supplier,
    UserSupplier,
    SendLog
};
use App\Modules\Admin\Mail\AccountCreated;
use App\Common\Models\Project\{
    ProjectSupplier
};
use App\Modules\Admin\Mail\{
    SupplierAuditPassMail,
    SupplierAuditRefuseMail,
    SupplierChangePassMail,
    SupplierChangeRefuseMail,
    SupplierFreezePassMail,
    SupplierUnFreezePassMail,
    SupplierUnFreezeRefuseMail,
    ComparePassMail,
    CompareAuditMail,
    CompareRefuseMail,
    BidBillPassMail,
    BidBillRefuseMail
};
use App\Modules\Admin\Repository\Project\ProjectDecisionFileRepo;

class SendMailJob extends Job {

    protected $response;
    protected $request;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $type) {
        $this->request = $request;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            switch ($this->type) {
                case 'BIDBILL':
                    return $this->sendBidBillMail($this->request);
                case 'BIDBILL_SIGN':
                    return $this->sendBidBillSignMail($this->request);
                case 'BIDBILL_TOBEPAY':
                    return $this->sendBidBillToBePayMail($this->request);
                case 'BIDBILL_CHECKNOT':
                    return $this->sendBidBillCheckNotMail($this->request);
                case 'INQUIRY':
                    return $this->sendInquiryMail($this->request);
                case 'SUPPLIERCHANGE':
                    return $this->supplierChange($this->request['emails'], $this->request['name'], $this->request['supplier_id']);
                case 'SUPPLIERSUBMIT':
                    return $this->supplierSubmit($this->request['emails'], $this->request['name'], $this->request['supplier_id']);
                case 'SUPPLIERPASS':
                    return $this->supplierPass($this->request['email'], $this->request['supplier_id']);
                case 'SUPPLIERREFUSE':
                    return $this->supplierRefuse($this->request['email'], $this->request['supplier_id']);
                case 'SUPPLIERFREEZEPASS':
                    return $this->supplierFreezePass($this->request['email'], $this->request['supplier_name'], $this->request['supplier_id']);
                case 'COMPAREAUDIT':
                    return $this->CompareAudit($this->request['email'], $this->request['purchaserName'], $this->request['compareId']);
                case 'COMPAREPASS':
                    return $this->ComparePass($this->request['email'], $this->request['purchaserName'], $this->request['data'], $this->request['inquiry_id']);
                case 'COMPAREREFUSE':
                    return $this->CompareRefuse($this->request['email'], $this->request['purchaserName'], $this->request['inquiry_id']);
                case 'SUPPLIERUNFREEZEPASS':
                    return $this->supplierUnFreezePass($this->request['email'], $this->request['supplier_name'], $this->request['supplier_id']);
                case 'SUPPLIERUNFREEZEREFUSE':
                    return $this->supplierUnFreezeRefuse($this->request['email'], $this->request['supplier_name'], $this->request['supplier_id']);
                case 'SUPPLIERCHANGEPASS':
                    return $this->supplierChangePass($this->request['email'], $this->request['supplier_id']);
                case 'SUPPLIERCHANGEREFUSE':
                    return $this->supplierChangeRefuse($this->request['email'], $this->request['supplier_id']);
                case 'SUPPLIERQUOTE':
                    return $this->supplierQuote($this->request['email'], $this->request['name'], $this->request['inquiyrTitle']);
                case 'BIDBILLPASS':
                    return $this->BidBillPass($this->request['email'], $this->request['title'], $this->request['id']);
                case 'BIDBILLREFUSE':
                    return $this->BidBillRefuse($this->request['email'], $this->request['title'], $this->request['id']);
                case 'PROJECT':
                    return $this->sendProjectMail($this->request);
                case 'WIN_PROJECT':
                    return $this->sendWinProjectMail($this->request);
                case 'FAIL_PROJECT':
                    return $this->sendFailProjectMail($this->request);
                case 'PROJECT_SIGN':
                    return $this->sendProjectSignMail($this->request);
                case 'PROJECT_QUOTE':
                    return $this->sendProjectQuoteMail($this->request);
                case 'PROJECT_UNQUOTE':
                    return $this->sendProjectUnQuoteMail($this->request);
                case 'PROJECT_UNSIGN':
                    return $this->sendProjectUnSignMail($this->request);
                case 'PROJECTPUBLISH':
                    return $this->sendProjectPublishMail($this->request);
                case 'PROJECTOPEN':
                    return $this->sendProjectOpenMail($this->request);
                case 'PROJECTEVA':
                    return $this->sendProjectEvaMail($this->request);
                case 'PROJECTSELECTED':
                    return $this->sendProjectSelectedMail($this->request);
                case 'PROJECT_OPEN_DEADLINE':
                    return $this->sendProjectOpenDeadMail($this->request);
                case 'USER_SEND':
                    return $this->sendUserMail($this->request);
                case 'PROJECT_PAYAUDIT':
                    return $this->sendProjectPayAuditMail($this->request);
            }
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . $ex->getMessage());
        }
    }

    public function sendBidBillMail($request) {
        if (empty($request['bidBillData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $bidBillData = $request['bidBillData'];
        $supplierIds = $request['supplierIds'];
        if ($bidBillData['bill_status'] !== 'C' || $bidBillData['biz_type'] != 2) {
            return;
        }
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $emails = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $emails[] = $email['email'];
        }
        if (empty($emails)) {
            return;
        }
        $org = ['name' => $bidBillData['name'], 'bidBillId' => $bidBillData['id']];
        $dataList = [];
        foreach ($emails AS $email) {
            $response = Mail::mailer('default')
                    ->send('mail.bidbill', $org, function (MailMessage $message) use ($email) {
                $message->to($email);
                $message->subject('【' . env('APP_NAME') . '】竞价报名通知');
            });
            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email,
                'title' => 'BIDBILL',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendBidBillToBePayMail($request) {
        if (empty($request['bidBillData']) || empty($request['id'])) {
            return;
        }
        $bidBillData = $request['bidBillData'];
        if ($bidBillData['bill_status'] !== 'C' || $bidBillData['biz_type'] != 2) {
            return;
        }
        $user = (new User)->getTable();
        $supplier = (new \App\Common\Models\BidBill\BidBillSupplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($supplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.enroll_id')
                    ->where('us.deleted_flag', 'N');
                })
                ->where('us.bid_bill_id', $request['id'])
                ->where('us.entry_status', 'L')
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $emails = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $emails[] = $email['email'];
        }
        if (empty($emails)) {
            return;
        }
        $org = ['name' => $bidBillData['name'], 'bidBillId' => $bidBillData['id']];
        $dataList = [];
        foreach ($emails AS $email) {

            $response = Mail::mailer('default')
                    ->send('mail.bidbillToPay', $org, function (MailMessage $message) use ($email) {
                $message->to($email);
                $message->subject('【' . env('APP_NAME') . '】竞价待缴费通知');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email,
                'title' => 'BIDBILL',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendBidBillCheckNotMail($request) {
        if (empty($request['bidBillData']) || empty($request['id'])) {
            return;
        }
        $bidBillData = $request['bidBillData'];
        if ($bidBillData['bill_status'] !== 'C') {
            return;
        }
        $user = (new User)->getTable();
        $supplier = (new \App\Common\Models\BidBill\BidBillSupplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($supplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.enroll_id')
                    ->where('us.deleted_flag', 'N');
                })
                ->where('us.bid_bill_id', $request['id'])
                ->where('us.entry_status', 'C')
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $emails = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $emails[] = $email['email'];
        }
        if (empty($emails)) {
            return;
        }
        $org = ['name' => $bidBillData['name'], 'bidBillId' => $bidBillData['id']];
        $dataList = [];
        foreach ($emails AS $email) {

            $response = Mail::mailer('default')
                    ->send('mail.bidbillCheckNot', $org, function (MailMessage $message) use ($email) {
                $message->to($email);
                $message->subject('【' . env('APP_NAME') . '】竞价资审未通过通知');
            });
            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email,
                'title' => 'BIDBILL',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendBidBillSignMail($request) {
        if (empty($request['bidBillData']) || empty($request['email']) || empty($request['supplierName'])) {
            return;
        }
        $bidBillData = $request['bidBillData'];
        $email = $request['email'];
        $supplierName = $request['supplierName'];
        $name = $bidBillData['name'];
        $org = ['bidBillId' => $bidBillData['id'], 'name' => $name, 'supplierName' => $supplierName];
        $response = Mail::mailer('default')
                ->send('mail.bidbillSign', $org, function (MailMessage $message) use ($email) {
            $message->to($email);
            $message->subject('【' . env('APP_NAME') . '】竞价报名通知');
        });


        $dataList[] = [
            'type' => $this->type,
            'message_to' => $email,
            'title' => '【' . env('APP_NAME') . '】竞价报名通知',
            'message' => json_encode($org),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        SendLog::insert($dataList);
    }

    public function sendUserMail($request) {
        if (empty($request['email']) || empty($request['phone'])) {
            return;
        }
        $response = Mail::to($request['email'])
                ->send(new AccountCreated(['email' => trim($request['email']),
            'phone' => trim($request['phone'])]));
        $dataList[] = [
            'type' => $this->type,
            'message_to' => $request['email'],
            'title' => '【' . env('APP_NAME') . '】新建账号通知',
            'message' => json_encode(['email' => trim($request['email']),
                'phone' => trim($request['phone'])]),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        SendLog::insert($dataList);
    }

    public function sendInquiryMail($request) {
        if (empty($request['inquiryData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $inquiryData = $request['inquiryData'];
        $inquiryId = $request['inquiryId'];
        $supplierIds = $request['supplierIds'];
        $orgName = $request['orgName'];

        if ($inquiryData['bill_status'] !== 'C' || $inquiryData['sup_scope'] != 2) {
            return;
        }
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $org = ['name' => $orgName, 'inquiryId' => $inquiryId];
        $emails = [];
        foreach ($emailList AS $email) {
            if (empty($email['email']) && !isEmail($email['email'])) {
                continue;
            }
            $emails[] = $email['email'];
        }
        if (empty($emails)) {
            return;
        }
        foreach ($emails AS $email) {
            Mail::mailer('default')
                    ->send('mail.inquiry', $org, function (MailMessage $message) use ($email) {
                        $message->to($email);
                        $message->subject('【' . env('APP_NAME') . '】已发布报价邀请');
                    });
        }
    }

    public function supplierChange($emailList, $name, $supplierId) {
        if (empty($emailList)) {
            return;
        }
        $emails = [];
        foreach ($emailList as $email) {
            if (!empty($email) && isEmail($email)) {
                $emails[] = $email;
            }
        }
        $type = 'email';
        $title = 'SupplierChange';
        $user = ['name' => $name, 'supplier_id' => $supplierId];
        foreach ($emails as $email) {
            $response = Mail::send('mail.supplierChange', $user, function (MailMessage $message) use ($email) {
                        $message->to($email);
                        $message->subject('【' . env('APP_NAME') . '】供应商已变更企业认证信息，请尽快登录系统审核');
                    });
            $data = [
                'type' => $type,
                'message_to' => $email,
                'title' => $title,
                'message' => json_encode($user),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            SendLog::insert($data);
        }
    }

    public function supplierSubmit($emailList, $name, $supplierId) {
        if (empty($emailList)) {
            return;
        }
        $emails = [];
        foreach ($emailList as $email) {
            if (!empty($email) && isEmail($email)) {
                $emails[] = $email;
            }
        }
        $type = 'email';
        $title = 'SupplierSubmit';
        $user = ['name' => $name, 'supplier_id' => $supplierId];
        foreach ($emails as $email) {
            $response = Mail::send('mail.supplierSubmit', $user, function (MailMessage $message) use ($email) {
                        $message->to($email);
                        $message->subject('【' . env('APP_NAME') . '】供应商已提交企业认证信息，请尽快登录系统审核');
                    });
            $data = [
                'type' => $type,
                'message_to' => $email,
                'title' => $title,
                'message' => json_encode($user),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            SendLog::insert($data);
        }
    }

    public function supplierPass($email, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierPass';
        $user = ['email' => $email, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierAuditPassMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierRefuse($email, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierRefuse';
        $user = ['email' => $email, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierAuditRefuseMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierFreezePass($email, $supplierName, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierPass';
        $user = ['email' => $email, 'supplier_name' => $supplierName, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierFreezePassMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function CompareAudit($email, $purchaserName, $compareId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'ComparePass';
        $user = ['email' => $email, 'purchaserName' => $purchaserName, 'compareId' => $compareId];
        $response = Mail::to($email)->send(new CompareAuditMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function ComparePass($email, $purchaserName, $data) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'ComparePass';
        $user = ['email' => $email, 'purchaserName' => $purchaserName, 'data' => $data];
        $response = Mail::to($email)->send(new ComparePassMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function CompareRefuse($email, $purchaserName) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'CompareRefuse';
        $user = ['email' => $email, 'purchaserName' => $purchaserName];
        $response = Mail::to($email)->send(new CompareRefuseMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierUnFreezePass($email, $supplierName, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierPass';
        $user = ['email' => $email, 'supplier_name' => $supplierName, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierUnFreezePassMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierUnFreezeRefuse($email, $supplierName, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierPass';
        $user = ['email' => $email, 'supplier_name' => $supplierName, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierUnFreezeRefuseMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierChangePass($email, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierPass';
        $user = ['email' => $email, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierChangePassMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierChangeRefuse($email, $supplierId) {
        $type = 'email';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $title = 'supplierPass';
        $user = ['email' => $email, 'supplier_id' => $supplierId];
        $response = Mail::to($email)->send(new SupplierChangeRefuseMail($user));
        SendLog::insertGetId([
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function supplierQuote($email, $name, $inquiyrTitle) {
        $type = 'email';
        $title = 'supplierQuote';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $user = ['name' => $name, 'email' => $email, 'inquiry_title' => $inquiyrTitle];
        $response = Mail::send('mail.supplierQuote', $user, function (MailMessage $message) use ($email) {
                    $message->to($email);
                    $message->subject('【' . env('APP_NAME') . '】报价通知');
                });
        $dataList[] = [
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        SendLog::insert($dataList);
    }

    public function BidBillPass($email, $bbTitle, $id) {
        $type = 'email';
        $title = 'BidBillPass';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $user = ['email' => $email, 'title' => $bbTitle, 'id' => $id];
        $response = Mail::to($email)->send(new BidBillPassMail($user));
        $dataList[] = [
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        SendLog::insert($dataList);
    }

    public function BidBillRefuse($email, $bbTitle, $id) {
        $type = 'email';
        $title = 'BidBillRefuse';
        if (empty($email) && !isEmail($email)) {
            return;
        }
        $user = ['email' => $email, 'title' => $bbTitle, 'id' => $id];
        $response = Mail::to($email)->send(new BidBillRefuseMail($user));
        $dataList[] = [
            'type' => $type,
            'message_to' => $email,
            'title' => $title,
            'message' => json_encode($user),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        SendLog::insert($dataList);
    }

    public function sendProjectMail($request) {

        if (empty($request['projectData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $supplierIds = $request['supplierIds'];
        $orgName = $request['orgName'];
        //1：提交立项 2：增补供应商
        if (empty($request['type'])) {
            if ($projectData['bill_status'] !== 'C' || $projectData['bid_mode_id'] != 2) {
                return;
            }
        } else {
            if ($projectData['bid_mode_id'] != 2) {
                return;
            }
        }
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new Supplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->join($supplier . ' as s', function($join) {
                    $join->on('s.id', '=', 'us.supplier_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,s.name,us.supplier_id,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }


        $emails = [];
        $org = ['name' => $projectData['name'], 'orgName' => $orgName, 'projectId' => $projectData['id']];
        $dataList = [];
        foreach ($emailList AS $email) {

            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $emails[] = $email['email'];
            $response = Mail::mailer('default')
                    ->send('mail.projectInvitation', $org, function (MailMessage $message) use ($email, $projectData) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】发布【' . $projectData['name'] . '】的招标邀请');
            });
            ProjectSupplier::where('supplier_id', $email['supplier_id'])
                    ->where('project_id', $projectData['id'])
                    ->update([
                        'invitation_status' => 'C'
            ]);
            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】发布【' . $projectData['name'] . '】的招标邀请',
                'message' => json_encode($org),
                'status' => 'C',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendWinProjectMail($request) {

        if (empty($request['projectData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $supplierIds = $request['supplierIds'];
        if ($projectData['bill_status'] !== 'C') {
            return;
        }
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new Supplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->join($supplier . ' as s', function($join) {
                    $join->on('s.id', '=', 'us.supplier_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,s.name,us.supplier_id,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        (new \App\Modules\Admin\Repository\SupplierBaseRepo)->setSuppliers($emailList);
        $emails = [];
        $org = ['name' => $projectData['name'], 'projectId' => $projectData['id']];
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $attachs = (new ProjectDecisionFileRepo)->getList($projectData['id']);
            $org = ['name' => $projectData['name'],
                'supplier_name' => $email['supplier_name'],
                'eva_reports' => !empty($attachs['eva_report']) ? $attachs['eva_report'] : [],
                'win_reports' => !empty($attachs['win_report']) ? $attachs['win_report'] : [],
                'attachs' => !empty($attachs['attachs']) ? $attachs['attachs'] : [],
            ];
            $emails[] = $email['email'];
            $response = Mail::mailer('default')
                    ->send('mail.projectWin', $org, function (MailMessage $message) use ($email) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】中标通知书');
            });
            ProjectSupplier::where('supplier_id', $email['supplier_id'])
                    ->where('project_id', $projectData['id'])
                    ->update([
                        'status' => 'F'
            ]);
            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】中标通知书',
                'message' => json_encode($org),
                'status' => 'C',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendFailProjectMail($request) {

        if (empty($request['projectData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $supplierIds = $request['supplierIds'];
        if ($projectData['bill_status'] !== 'C') {
            return;
        }

        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new Supplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->join($supplier . ' as s', function($join) {
                    $join->on('s.id', '=', 'us.supplier_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,s.name,us.supplier_id,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }

        (new \App\Modules\Admin\Repository\SupplierBaseRepo)->setSuppliers($emailList);
        $emails = [];
        $org = ['name' => $projectData['name'], 'projectId' => $projectData['id']];
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['name' => $projectData['name'], 'supplier_name' => $email['supplier_name']];
            $emails[] = $email['email'];
            $response = Mail::mailer('default')
                    ->send('mail.projectThanks', $org, function (MailMessage $message) use ($email) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】感谢信');
            });
            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】感谢信',
                'message' => json_encode($org),
                'status' => 'C',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendProjectUnQuoteMail($request) {
        if (empty($request['projectData']) || empty($request['email']) || empty($request['supplierName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $email = $request['email'];
        $supplierName = $request['supplierName'];
        $name = $projectData['name'];
        $org = ['projectId' => $projectData['id'], 'name' => $name, 'supplierName' => $supplierName];
        $response = Mail::mailer('default')
                ->send('mail.projectUnQuote', $org, function (MailMessage $message) use ($email, $org) {
            $message->to($email);
            $message->subject('【' . env('APP_NAME') . '】【' . $org['name'] . '】的招标项目，【' . $org['supplierName'] . '】未投标');
        });
        $dataList[] = [
            'type' => $this->type,
            'message_to' => $email,
            'title' => '【' . env('APP_NAME') . '】【' . $org['name'] . '】的招标项目，【' . $org['supplierName'] . '】未投标',
            'message' => json_encode($org),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        SendLog::insert($dataList);
    }

    public function sendProjectQuoteMail($request) {
        if (empty($request['projectData']) || empty($request['email']) || empty($request['supplierName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $email = $request['email'];
        $supplierName = $request['supplierName'];
        $name = $projectData['name'];
        $org = ['projectId' => $projectData['id'], 'name' => $name, 'supplierName' => $supplierName];
        $response = Mail::mailer('default')
                ->send('mail.projectQuote', $org, function (MailMessage $message) use ($email, $org) {
            $message->to($email);
            $message->subject('【' . env('APP_NAME') . '】【' . $org['name'] . '】的招标项目，【' . $org['supplierName'] . '】已投标');
        });
        $dataList[] = [
            'type' => $this->type,
            'message_to' => $email,
            'title' => '【' . env('APP_NAME') . '】【' . $org['name'] . '】的招标项目，【' . $org['supplierName'] . '】已投标',
            'message' => json_encode($org),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        SendLog::insert($dataList);
    }

    public function sendProjectSignMail($request) {
        if (empty($request['projectData']) || empty($request['email']) || empty($request['supplierName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $email = $request['email'];
        $supplierName = $request['supplierName'];
        $name = $projectData['name'];
        $org = ['projectId' => $projectData['id'], 'name' => $name, 'supplierName' => $supplierName];
        $response = Mail::mailer('default')
                ->send('mail.projectSign', $org, function (MailMessage $message) use ($email, $org) {
            $message->to($email);
            $message->subject('【' . env('APP_NAME') . '】【' . $org['name'] . '】【' . $org['supplierName'] . '】已报名');
        });


        $dataList[] = [
            'type' => $this->type,
            'message_to' => $email,
            'title' => '【' . env('APP_NAME') . '】【' . $org['name'] . '】【' . $org['supplierName'] . '】已报名',
            'message' => json_encode($org),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        SendLog::insert($dataList);
    }

    public function sendProjectUnSignMail($request) {
        if (empty($request['projectData']) || empty($request['email']) || empty($request['supplierName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $email = $request['email'];
        $supplierName = $request['supplierName'];
        $name = $projectData['name'];
        $org = ['projectId' => $projectData['id'], 'name' => $name, 'supplierName' => $supplierName];
        $response = Mail::mailer('default')
                ->send('mail.projectUnSign', $org, function (MailMessage $message) use ($email, $org) {
            $message->to($email);
            $message->subject('【' . env('APP_NAME') . '】【' . $org['name'] . '】【' . $org['supplierName'] . '】不报名');
        });
        $dataList[] = [
            'type' => $this->type,
            'message_to' => $email,
            'title' => '【' . env('APP_NAME') . '】【' . $org['name'] . '】【' . $org['supplierName'] . '】不报名',
            'message' => json_encode($org),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        SendLog::insert($dataList);
    }

    public function sendProjectPublishMail($request) {
        if (empty($request['projectData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $supplierIds = $request['supplierIds'];
        $orgName = $request['orgName'];
        $name = $projectData['name'];
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new Supplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->join($supplier . ' as s', function($join) {
                    $join->on('s.id', '=', 'us.supplier_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,s.name,us.supplier_id,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        (new \App\Modules\Admin\Repository\SupplierBaseRepo)->setSuppliers($emailList);
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['projectId' => $projectData['id'], 'name' => $name, 'orgName' => $orgName];
            $response = Mail::mailer('default')
                    ->send('mail.projectPublish', $org, function (MailMessage $message) use ($email, $org) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】发布【' . $org['name'] . '】的招标项目，标书已发布。');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】发布【' . $org['name'] . '】的招标项目，标书已发布。',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendProjectOpenMail($request) {
        if (empty($request['projectData']) || empty($request['userIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $userIds = $request['userIds'];
        $orgName = $request['orgName'];
        $name = $projectData['name'];
        $user = (new User)->getTable();
        $emailObj = User::from($user . ' as u')
                ->whereIn('u.user_id', $userIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['projectId' => $projectData['id'], 'name' => $name, 'orgName' => $orgName];
            $response = Mail::mailer('default')
                    ->send('mail.projectOpen', $org, function (MailMessage $message) use ($email, $org) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】【' . $org['name'] . '】的评标通知！');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】【' . $org['name'] . '】的评标通知！',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendProjectEvaMail($request) {
        if (empty($request['projectData']) || empty($request['userIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $userIds = $request['userIds'];
        $orgName = $request['orgName'];
        $name = $projectData['name'];
        $user = (new User)->getTable();
        $emailObj = User::from($user . ' as u')
                ->whereIn('u.user_id', $userIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['projectId' => $projectData['id'], 'name' => $name, 'orgName' => $orgName];
            $response = Mail::mailer('default')
                    ->send('mail.projectEvaluation', $org, function (MailMessage $message) use ($email, $org) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】【' . $org['name'] . '】的定标通知！');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】【' . $org['name'] . '】的定标通知！',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendProjectSelectedMail($request) {
        if (empty($request['projectData']) || empty($request['supplierIds']) || empty($request['orgName'])) {
            return;
        }
        $projectData = $request['projectData'];
        $supplierIds = $request['supplierIds'];
        $orgName = $request['orgName'];
        $name = $projectData['name'];
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new Supplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->join($supplier . ' as s', function($join) {
                    $join->on('s.id', '=', 'us.supplier_id');
                })
                ->whereIn('us.supplier_id', $supplierIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,s.name,us.supplier_id,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        (new \App\Modules\Admin\Repository\SupplierBaseRepo)->setSuppliers($emailList);
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['projectId' => $projectData['id'], 'name' => $name, 'orgName' => $orgName];
            $response = Mail::mailer('default')
                    ->send('mail.projectSelected', $org, function (MailMessage $message) use ($email, $org) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】恭喜你！成功入围发布【' . $org['name'] . '】的招标项目。');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】恭喜你！成功入围发布【' . $org['name'] . '】的招标项目。',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendProjectOpenDeadMail($request) {
        if (empty($request['projectData']) || empty($request['userIds'])) {
            return;
        }
        $projectData = $request['projectData'];
        $userIds = $request['userIds'];
        $name = $projectData['name'];
        $user = (new User)->getTable();
        $emailObj = User::from($user . ' as u')
                ->whereIn('u.user_id', $userIds)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['projectId' => $projectData['id'], 'name' => $name];
            $response = Mail::mailer('default')
                    ->send('mail.projectOpenDeadline', $org, function (MailMessage $message) use ($email, $org) {
                $message->to($email['email']);
                $message->subject('招标项目【' . $org['name'] . '】将在20分钟后开标。');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '招标项目【' . $org['name'] . '】将在20分钟后开标。',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

    public function sendProjectPayAuditMail($request) {
        if (empty($request['projectId']) || empty($request['supplierId']) || empty($request['orgName'])) {
            return;
        }
        $projectId = $request['projectId'];
        $supplierId = $request['supplierId'];
        $orgName = $request['orgName'];
        $name = $request['name'];
        $user = (new User)->getTable();
        $userSupplier = (new UserSupplier)->getTable();
        $supplier = (new Supplier)->getTable();
        $emailObj = User::from($user . ' as u')
                ->join($userSupplier . ' as us', function($join) {
                    $join->on('u.user_id', '=', 'us.user_id');
                })
                ->join($supplier . ' as s', function($join) {
                    $join->on('s.id', '=', 'us.supplier_id');
                })
                ->where('us.supplier_id', $supplierId)
                ->whereRaw('(u.email<>\'\' AND u.email IS NOT NULL)')
                ->selectRaw('u.realname,u.email,s.name,us.supplier_id,u.user_id')
                ->groupBy('u.user_id')
                ->get();
        if (empty($emailObj)) {
            return [];
        }
        $emailList = $emailObj->toArray();
        if (empty($emailList)) {
            return;
        }
        (new \App\Modules\Admin\Repository\SupplierBaseRepo)->setSuppliers($emailList);
        $dataList = [];
        foreach ($emailList AS $email) {
            if (empty(trim($email['email'])) || !isEmail(trim($email['email']))) {
                continue;
            }
            $org = ['projectId' => $projectId, 'name' => $name, 'orgName' => $orgName];
            $response = Mail::mailer('default')
                    ->send('mail.projectPay', $org, function (MailMessage $message) use ($email, $org) {
                $message->to($email['email']);
                $message->subject('【' . env('APP_NAME') . '】恭喜你！您参与的【' . $org['name'] . '】的招标项目缴费已确认，请尽快登录系统完成标书下载和投标。');
            });

            $dataList[] = [
                'type' => $this->type,
                'message_to' => $email['email'],
                'title' => '【' . env('APP_NAME') . '】恭喜你！您参与的【' . $org['name'] . '】的招标项目缴费已确认，请尽快登录系统完成标书下载和投标。',
                'message' => json_encode($org),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($dataList)) {
            SendLog::insert($dataList);
        }
    }

}
