<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Models\{
    BidBill\BidBill,
    BidBill\Sub,
    BidBill\BidBillQuote,
    BidBill\BidBillSupplier,
    SendLog,
    Message,
    MessageReceiver,
    User
};
use App\Modules\Admin\Repository\{
    BidBill\BidBillHallRepo,
    BidBill\BidBillRepo,
    BidBill\BidBillPayRepo,
    UserRepo,
    SupplierContactRepo
};
use Illuminate\Support\Facades\DB;
use Illuminate\Mail\Message AS MailMessage;
use Illuminate\Support\Facades\Mail;

// 后台用户数据导入
class BidBillCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidbill:operate {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bidbill operate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $action = $this->argument('action');
        switch ($action) {
            case 'deadline':
                try {
                    $this->deadline();
                } catch (Exception $ex) {
                    sendDingTalk([
                        'type' => 'userNo',
                        'toUser' => '016417',
                        'msgType' => 'text',
                        'message' => $ex->getMessage(),
                        'title' => '【' . env('APP_NAME') . '】【竞价】“截止通知”报错',
                    ]);
                }
                break;
            case 'expired':
                try {
                    $this->expired();
                } catch (Exception $ex) {
                    sendDingTalk([
                        'type' => 'userNo',
                        'toUser' => '016417',
                        'msgType' => 'text',
                        'message' => $ex->getMessage(),
                        'title' => '【' . env('APP_NAME') . '】【竞价】“报名截止”报错',
                    ]);
                }
                break;
            case 'be_about':
                try {
                    $this->beAbout();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . $action . '    ' . $ex->getMessage());
                }
                break;
            case 'no_bid':
                try {
                    $this->NoBid();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . $action . '    ' . $ex->getMessage());
                }
                break;
            case 'evaluation':
                try {
                    $this->evaluation();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . $action . '    ' . $ex->getMessage());
                }
                break;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function NoBid() {
        $bidBillObj = BidBill::selectRaw('bill_no,name,check_type,org_id,person_id,enroll_date,id,org_id,id')
                ->whereRaw('bill_status=\'C\'')
                ->where('bid_status', 'C')
                ->whereRaw('open_date> FROM_UNIXTIME(UNIX_TIMESTAMP()+bid_time*30+30,\'%Y-%m-%d %H:%i:%s\') '
                        . 'AND open_date<FROM_UNIXTIME(UNIX_TIMESTAMP()+bid_time*30-30,\'%Y-%m-%d %H:%i:%s\')')
                ->get();
        if (empty($bidBillObj)) {
            return;
        }
        $bidBillList = $bidBillObj->toArray();
        (new UserRepo)->setEmails($bidBillList, 'person_id');
        $bossUrl = env('BOSS_URL');
        foreach ($bidBillList as $bidBill) {
            $this->sendSuppliers($bossUrl, $bidBill);
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBill['id'],
                        'sender_id' => 1,
                        'message_type' => 'SYSTEM',
                        'message_title' => '供应商未【竞价】通知',
                        'message' => '【' . $bidBill['name'] . '】【竞价】已开启，有供应商未参与【竞价】，请及时处理。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);

            MessageReceiver::insert([
                'message_id' => $messageId,
                'receiver_id' => $bidBill['person_id'],
                'org_id' => $bidBill['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if (empty($bidBill['email'])) {
                continue;
            }

            $response = Mail::mailer('default')
                    ->send('mail.bidbillNoBid', $bidBill, function (MailMessage $message) use ($bidBill) {
                $message->to($bidBill['email']);
                $message->subject('【' . env('APP_NAME') . '】供应商未【竞价】通知');
            });

            SendLog::insert([
                'type' => 'BIDBILL_BEABOUT',
                'message_to' => $bidBill['email'],
                'title' => '供应商未【竞价】通知',
                'message' => json_encode($bidBill),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function beAbout() {
        $bidBillObj = BidBill::selectRaw('bill_no,name,check_type,org_id,person_id,enroll_date,id,org_id,id')
                ->whereRaw('bill_status=\'C\'')
                ->whereRaw('((bid_status=\'B\' AND deposit_flag=\'N\' AND check_type=\'1\'  )'
                        . 'OR(bid_status=\'K\' AND deposit_flag=\'Y\') OR'
                        . '(bid_status=\'I\' AND deposit_flag=\'N\' AND check_type=\'3\'  )) ')
                ->whereRaw('(open_date<\'' . date('Y-m-d H:i:s', strtotime('-10 minutes')) . '\' '
                        . 'AND open_date>\'' . date('Y-m-d H:i:s', strtotime('-8 minutes')) . '\')')
                ->get();
        if (empty($bidBillObj)) {
            return;
        }
        $bidBillList = $bidBillObj->toArray();
        (new UserRepo)->setEmails($bidBillList, 'person_id');
        $bossUrl = env('BOSS_URL');
        foreach ($bidBillList as $bidBill) {

            $this->sendSuppliers($bossUrl, $bidBill);
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBill['id'],
                        'sender_id' => 1,
                        'message_type' => 'SYSTEM',
                        'message_title' => '【竞价】还有10分钟即将启动',
                        'message' => '【' . $bidBill['name'] . '】还有10分钟即将启动，如需调整【竞价】启动时间，请尽快前往系统处理。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);

            MessageReceiver::insert([
                'message_id' => $messageId,
                'receiver_id' => $bidBill['person_id'],
                'org_id' => $bidBill['org_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if (empty($bidBill['email'])) {
                continue;
            }

            $response = Mail::mailer('default')
                    ->send('mail.bidbillBeAbout', $bidBill, function (MailMessage $message) use ($bidBill) {
                $message->to($bidBill['email']);
                $message->subject('【' . env('APP_NAME') . '】【竞价】还有10分钟即将启动');
            });

            SendLog::insert([
                'type' => 'BIDBILL_BEABOUT',
                'message_to' => $bidBill['email'],
                'title' => '【竞价】还有10分钟即将启动',
                'message' => json_encode($bidBill),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function sendSuppliers($bossUrl, $bidBill) {

        $supplierObj = BidBillSupplier::selectRaw('supplier_id,enroll_id')
                ->where('deleted_flag', 'N')
                ->where('bid_bill_id', $bidBill['id'])
                ->where('allow_bid', 1)
                ->where('enroll_id', '>', 0)
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $supplierList = $supplierObj->toArray();
        if (empty($supplierList)) {
            return;
        }

        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/biddingManage/biddingDetail?id=' . $bidBill['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '【竞价】还有10分钟即将启动',
                    'message' => '【' . $bidBill['name'] . '】还有10分钟即将启动，请做好报价准备。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        foreach ($supplierList as $supplier) {
            MessageReceiver::insert([
                'message_id' => $messageId,
                'receiver_id' => $supplier['enroll_id'],
                'supplier_id' => $supplier['supplier_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if (empty($bidBill['email'])) {
                return;
            }
        }
        $enrollIds = array_column($supplierList, 'enroll_id');
        $emails = User::whereIn('user_id', $enrollIds)
                ->where('enable', '1')
                ->where('deleted_flag', 'N')
                ->pluck('email');
        if (empty($emails)) {
            return;
        }
        $dataList = [];
        foreach ($emails as $email) {
            if (empty(trim($email)) || !isEmail(trim($email))) {
                continue;
            }
            $response = Mail::mailer('default')
                    ->send('mail.bidbillToBeAbout', $bidBill, function (MailMessage $message) use ($email) {
                $message->to($email);
                $message->subject('【' . env('APP_NAME') . '】【竞价】还有10分钟即将启动');
            });
            $dataList[] = [
                'type' => 'BIDBILL_BEABOUT',
                'message_to' => $bidBill['email'],
                'title' => '【竞价】还有10分钟即将启动',
                'message' => json_encode($bidBill),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (empty($dataList)) {
            return;
        }
        SendLog::insert($dataList);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function deadline() {
        $bidBillObj = BidBill::selectRaw('bill_no,name,check_type,org_id,person_id,enroll_date,id')
                ->whereRaw('bill_status=\'C\'')
                ->whereRaw('bid_status=\'A\'')
                ->whereRaw('biz_type=\'2\'')
                ->whereRaw('enroll_date between \'' . date('Y-m-d H:i:s', strtotime('-1 hours'))
                        . '\' AND \'' . date('Y-m-d H:i:s') . '\'')
//                ->whereRaw('enroll_date<\'' . date('Y-m-d H:i:s', strtotime('-1 hours')) . '\'')
//          ->whereRaw('deleted_flag=\'N\'')
                ->get();
        if (empty($bidBillObj)) {
            return;
        }
        $bidBillList = $bidBillObj->toArray();
        $bossUrl = env('BOSS_URL');
        foreach ($bidBillList as $bidBill) {
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_url' => $bossUrl . '/front/#/bidbillRate/bidbillDetails?id=' . $bidBill->id,
                        'sender_id' => 1,
                        'message_type' => 'SYSTEM',
                        'message_title' => '【竞价】单通知',
                        'message' => '【竞价】单：【' . $bidBill->bill_no . '】，【' . $bidBill->name . '】，'
                        . '还有一个小时到截止时间了，截止时间：【' . $bidBill->enroll_date . '】。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);
            $data = [
                'message_id' => $messageId,
                'receiver_id' => $bidBill->person_id,
                'org_id' => $bidBill->org_id,
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ];
            MessageReceiver::insert($data);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function evaluation() {
        $subTable = (new Sub)->getTable();
        $bidBillTable = (new BidBill)->getTable();
        DB::beginTransaction();
        $lastQuoteQuery = BidBillQuote::selectRaw('bid_bill_id,max(quote_date) AS last_quote_date,GROUP_CONCAT(DISTINCT supplier_id) AS supplier_ids')
                ->groupBy('bid_bill_id');
        $bidBillObj = BidBill::from($bidBillTable . ' AS bb')
                ->selectRaw('bb.bid_time,bb.last_time,bb.id,bb.delay_time,org_id,person_id,'
                        . 'bs.pause_start_at,bs.bid_rest_at,open_date,last_quote_date,max.supplier_ids')
                ->join($subTable . ' as bs', function ($join) {
                    $join->on('bs.bid_bill_id', '=', 'bb.id');
                })
                ->leftJoinSub($lastQuoteQuery, 'max', function ($join) {
                    $join->on('bb.id', '=', 'max.bid_bill_id');
                })
                ->whereRaw('bill_status=\'C\'')
                ->whereRaw('bid_status=\'C\'')
                ->whereRaw('(open_date<FROM_UNIXTIME( UNIX_TIMESTAMP()-bb.bid_time*60,\'%Y-%m-%d %H:%i:%s\') '
                        . ' OR (bs.pause_start_at<FROM_UNIXTIME( UNIX_TIMESTAMP()-bs.bid_rest_at,\'%Y-%m-%d %H:%i:%s\'))'
                        . ' OR (max.last_quote_date<FROM_UNIXTIME( UNIX_TIMESTAMP()-bb.delay_time*60,\'%Y-%m-%d %H:%i:%s\'))'
                        . ')')
                ->get();
        if (empty($bidBillObj)) {
            return;
        }
        $bidBillList = $bidBillObj->toArray();
        if (empty($bidBillList)) {
            return;
        }
        $supplierIdArr = array_column($bidBillList, 'supplier_ids', 'id');
        $dataList = [];
        $bossUrl = env('BOSS_URL');
        foreach ($bidBillObj as $bidBill) {

            $leftTime = (new BidBillHallRepo)->getLeftTime($bidBill, $bidBill->id, $bidBill->last_quote_date);
            if ($leftTime > 0) {
                continue;
            }

            $supplierIds = !empty($supplierIdArr[$bidBill->id]) ? explode(',', $supplierIdArr[$bidBill->id]) : [];
            wsSendMsg($bidBill->id, 'evaluation', [
                'leftTime' => 0,
                'lastQuoteTime' => $bidBill->last_quote_date,
                'message' => '评标中',
            ]);
            if (!empty($supplierIds)) {
                BidBillSupplier::where('bid_bill_id', $bidBill->id)
                        ->where('entry_status', 'M')
                        ->whereIn('supplier_id', $supplierIds)
                        ->update([
                            'entry_status' => 'Q',
                ]);
                BidBillSupplier::where('bid_bill_id', $bidBill->id)
                        ->where('entry_status', 'M')
                        ->whereNotIn('supplier_id', $supplierIds)
                        ->update([
                            'entry_status' => 'J',
                ]);
                $dataList[] = [
                    'id' => $bidBill->id,
                    'bid_status' => 'D',
                    'cfm_status' => 'B',
                ];
                (new BidBillRepo)->sendFinished($bossUrl, $bidBill);
            } else {
                BidBillSupplier::where('bid_bill_id', $bidBill->id)
                        ->where('entry_status', 'M')
                        ->update([
                            'entry_status' => 'J',
                ]);
                $dataList[] = [
                    'id' => $bidBill->id,
                    'bid_status' => 'G',
                    'cfm_status' => 'C',
                ];
                Sub::where('bid_bill_id', $bidBill->id)
                        ->update([
                            'terminate' => '到【竞价】截止时间没有供应商报价',
                            'terminate_at' => date('Y-m-d H:i:s'),
                            'finished_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        BidBill::upsert($dataList, ['id'], ['bid_status', 'cfm_status']);
        DB::commit();
    }

    public function expired() {
//        $supplierTable = (new Supplier)->getTable();
        $bidBillTable = (new BidBill)->getTable();
        $bidBillObj = BidBill::from($bidBillTable . ' as b')
                ->selectRaw('b.id,b.check_type,b.deposit_flag,b.bill_no,b.name,'
                        . 'b.org_id,cash_deposit,person_id')
                ->where('b.enroll_date', '<=', date('Y-m-d H:i:s'))
                ->where('bid_status', 'A')
                ->get();
        if (empty($bidBillObj)) {
            return;
        }
        DB::beginTransaction();
        $bidBillList = $bidBillObj->toArray();
        (new UserRepo)->setEmails($bidBillList, 'person_id');
        $bidbillNames = array_column($bidBillList, 'name', 'id');
        $bidbillOrgIds = array_column($bidBillList, 'org_id', 'id');
        $bidbillNos = array_column($bidBillList, 'bill_no', 'id');
        $depositFlags = array_column($bidBillList, 'deposit_flag', 'id');
        $checkTypes = array_column($bidBillList, 'check_type', 'id');
        $cashDeposits = array_column($bidBillList, 'cash_deposit', 'id');
        $bossUrl = env('BOSS_URL');
        $bidBillIds = array_column($bidBillList, 'id');
        BidBill::whereIn('id', $bidBillIds)
                ->update(['bid_status' => 'I',
                    'updated_at' => date('Y-m-d H:i:s')]);
        $supplierObj = BidBillSupplier::whereIn('bid_bill_id', $bidBillIds)
                ->where('deleted_flag', 'N')
                ->whereIn('entry_status', ['Y', 'A', 'WQR', 'L'])
                ->get();
        if (empty($supplierObj)) {
            DB::commit();
            return;
        }
        $bidBillArr = [];
        foreach ($bidBillList as $bidBill) {
            $bidBillArr[$bidBill['id']] = $bidBill;
            BidBill::where('id', $bidBill['id'])
                    ->update(['bid_status' => 'I',
                        'updated_at' => date('Y-m-d H:i:s')]);
        }
        $bidBills = [];
        $suplierList = $supplierObj->toArray();
        $dataList = $payList = [];
        foreach ($suplierList AS $suplier) {
            if ($suplier['entry_status'] === 'T' || $suplier['entry_status'] === 'N' || $suplier['entry_status'] === 'WCY') {
                $dataList[] = [
                    'id' => $suplier['id'],
                    'entry_status' => 'WCY',
                    'allow_bid' => '0',
                ];
                continue;
            }
            $bidBillId = $suplier['bid_bill_id'];
            if ($suplier['entry_status'] == 'WQR') {
                $bidBills[$bidBillId] = empty($bidBills[$bidBillId]) ? 1 : $bidBills[$bidBillId] + 1;
            }
            $depositFlag = !empty($depositFlags[$bidBillId]) ? $depositFlags[$bidBillId] : 'N';
            $checkType = !empty($checkTypes[$bidBillId]) ? $checkTypes[$bidBillId] : 3;
            $bidbillName = !empty($bidbillNames[$bidBillId]) ? $bidbillNames[$bidBillId] : null;
            $orgId = !empty($bidbillOrgIds[$bidBillId]) ? $bidbillOrgIds[$bidBillId] : null;
            $bidbillNo = !empty($bidbillNos[$bidBillId]) ? $bidbillNos[$bidBillId] : null;
            $cashDeposit = !empty($cashDeposits[$bidBillId]) ? $cashDeposits[$bidBillId] : null;
            $dataList[] = [
                'id' => $suplier['id'],
                'entry_status' => $checkType == 1 ? 'A' : ( $depositFlag === 'Y' ? 'L' : 'Y'),
                'allow_bid' => $depositFlag === 'N' && $checkType == 3 ? '1' : '0',
            ];

            if ($depositFlag === 'Y' && $checkType == '3' && $suplier['entry_status'] !== 'WQR') {
                $contact = (new SupplierContactRepo)
                        ->getDefaultContact($suplier['supplier_id']);
                $payList[] = [
                    'bill_no' => (new BidBillPayRepo)->getBidBillPayNo(),
                    'bid_bill_id' => $suplier['bid_bill_id'],
                    'bid_bill_no' => $bidbillNo,
                    'bid_bill_name' => $bidbillName,
                    'org_id' => $orgId,
                    'supplier_id' => $suplier['supplier_id'],
                    'sure_amount' => $cashDeposit,
                    'bill_status' => 'A',
                    'contact_name' => !empty($contact['contact_name']) ? $contact['contact_name'] : '',
                    'contact_phone' => !empty($contact['phone']) ? $contact['phone'] : '',
                    'contact_email' => !empty($contact['email']) ? $contact['email'] : '',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        if (!empty($dataList)) {
            BidBillSupplier::upsert($dataList, ['id'], ['entry_status', 'allow_bid']);
        }
        if (!empty($bidBills)) {
            $FbidBillIds = array_keys($bidBills);
            BidBill::whereIn('id', $FbidBillIds)
                    ->update(['bid_status' => 'L',
                        'updated_at' => date('Y-m-d H:i:s')]);
        }
        foreach ($bidBillList as $bidBill) {
            if ($bidBill['check_type'] == '1') {
                $this->sendCheck($bossUrl, $bidBillArr[$bidBillId]);
            }
        }

        DB::commit();
    }

    public function sendCheck($bossUrl, $bidBill) {
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBill['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '【竞价】资审通知',
                    'message' => '【' . $bidBill['name'] . '】已报名截止，请及时完成资审。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);

        MessageReceiver::insert([
            'message_id' => $messageId,
            'receiver_id' => $bidBill['person_id'],
            'org_id' => $bidBill['org_id'],
            'read_flag' => 'N',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        if (empty($bidBill['email'])) {
            return;
        }
        $response = Mail::mailer('default')
                ->send('mail.bidbillCheck', $bidBill, function (MailMessage $message) use ($bidBill) {
            $message->to($bidBill['email']);
//            $message->to('zhongyg@erui.com');
            $message->subject('【' . env('APP_NAME') . '】【竞价】资审通知');
        });

        SendLog::insert([
            'type' => 'BIDBILL_BEABOUT',
            'message_to' => $bidBill['email'],
            'title' => '【竞价】资审通知',
            'message' => json_encode($bidBill),
            'status' => '',
            'return' => json_encode($response),
            'send_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function sendPay($bossUrl, $bidBill) {

        $supplierObj = BidBillSupplier::where('bid_bill_id', $bidBill['id'])
                ->where('deleted_flag', 'N')
                ->where('entry_status', 'L')
                ->get();
        if (empty($supplierObj)) {
            return;
        }
        $messageId = Message::insertGetId([
                    'receiver_type' => 'PURCHASER',
                    'content_url' => $bossUrl . '/front/#/bidding/BiddingDetails?id=' . $bidBill['id'],
                    'sender_id' => 1,
                    'message_type' => 'SYSTEM',
                    'message_title' => '【竞价】待缴费通知',
                    'message' => '【' . $bidBill['name'] . '】已开启收取保证金，请及时缴费。',
                    'created_at' => date('Y-m-d H:i:s'),
        ]);
        $supplierList = $supplierObj->toArray();
        foreach ($supplierList as $supplier) {
            MessageReceiver::insert([
                'message_id' => $messageId,
                'receiver_id' => $supplier['enroll_id'],
                'supplier_id' => $supplier['supplier_id'],
                'read_flag' => 'N',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        if (empty($bidBill['email'])) {
            return;
        }
        $enrollIds = array_column($supplierList, 'enroll_id');
        $emailObj = User::whereIn('user_id', $enrollIds)
                ->whereRaw('(email<>\'\' AND email IS NOT NULL)')
                ->selectRaw('realname,email')
                ->groupBy('user_id')
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
        $dataList = [];
        foreach ($emails AS $email) {
            $response = Mail::mailer('default')
                    ->send('mail.bidBillStayPay', $bidBill, function (MailMessage $message) use ($email) {
                $message->to($email);
                $message->subject('【' . env('APP_NAME') . '】【竞价】待缴费通知');
            });
            $dataList[] = [
                'type' => 'BIDBILL_PENDING',
                'message_to' => trim($email),
                'title' => '【竞价】待缴费通知',
                'message' => json_encode($bidBill),
                'status' => '',
                'return' => json_encode($response),
                'send_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        if (empty($dataList)) {
            return;
        }
        SendLog::insert($dataList);
    }

}
