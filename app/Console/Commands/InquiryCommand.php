<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Models\{
    Inquiry\Inquiry,
    Inquiry\Sub,
    Inquiry\Supplier,
    Quote\Quote,
    Message,
    MessageReceiver
};

// 后台用户数据导入
class InquiryCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inquiry:operate {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'inquiry operate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $action = $this->argument('action');
        switch ($action) {
            case 'opening':
                try {
                    $this->opening();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . $action . '    ' . $ex->getMessage());
                }
                break;
            case 'deadline':
                try {
                    $this->deadline();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . '   ' . $action . '    ' . $ex->getMessage());
                }
                break;
            case 'expired':
                try {
                    $this->expired();
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
    public function opening() {
        $inquiryIds = Inquiry::whereRaw('bill_status=\'C\'')
                ->whereRaw('biz_status=\'A\'')
                ->whereRaw('open_type=\'2\'')
                ->whereRaw('end_date<\'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('deleted_flag=\'N\'')
                ->pluck('id');
        if (empty($inquiryIds)) {
            return;
        }
        Inquiry::whereRaw('bill_status=\'C\'')
                ->whereRaw('biz_status=\'A\'')
                ->whereRaw('open_type=\'2\'')
                ->whereRaw('end_date <\'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('deleted_flag=\'N\'')
                ->update(['biz_status' => 'B',
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
        Sub::whereIn('inquiry_id', $inquiryIds)->update([
            'open_date' => date('Y-m-d H:i:s'),
        ]);
        Supplier::whereIn('inquiry_id', $inquiryIds)
                ->where('supplier_biz_status', 'B')
                ->where('entry_status', 'A')
                ->update([
                    'entry_status' => 'B',
                    'updated_at' => date('Y-m-d H:i:s')
        ]);
        Quote::whereIn('inquiry_id', $inquiryIds)
                ->where('bill_status', 'C')
                ->whereRaw('deleted_flag=\'N\'')
                ->where('biz_status', 'A')
                ->where('deleted_flag', 'N')
                ->update([
                    'biz_status' => 'B',
                    'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function deadline() {
        $inquiryObj = Inquiry::selectRaw('bill_no,inquiry_title,org_id,person_id,end_date,id')
                ->whereRaw('bill_status=\'C\'')
                ->whereRaw('biz_status=\'A\'')
//          ->whereRaw('open_type IN (\'2\',\'3\',\'4\')
                ->whereRaw('end_date<\'' . date('Y-m-d H:i:s', strtotime('+1 hours')) . '\'')
                ->whereRaw('end_date>\'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('deleted_flag=\'N\'')
                ->get();
        if (empty($inquiryObj)) {
            return;
        }
        $inquiryList = $inquiryObj->toArray();
        $bossUrl = env('BOSS_URL');
        foreach ($inquiryList as $inquiry) {
            $messageId = Message::insertGetId([
                        'receiver_type' => 'PURCHASER',
                        'content_url' => $bossUrl . '/front/#/inquiryRate/inquiryDetails?id=' . $inquiry['id'],
                        'sender_id' => 1,
                        'message_type' => 'SYSTEM',
                        'message_title' => '【询价】单通知',
                        'message' => '【询价】单：【' . $inquiry['bill_no'] . '】，【' . $inquiry['inquiry_title'] . '】，'
                        . '还有一个小时到截止时间了，截止时间：【' . $inquiry['end_date'] . '】。',
                        'created_at' => date('Y-m-d H:i:s'),
            ]);
            $data = [
                'message_id' => $messageId,
                'receiver_id' => $inquiry['person_id'],
                'org_id' => $inquiry['org_id'],
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
    public function expired() {
        $supplierTable = (new Supplier)->getTable();
        $inquiryTable = (new Inquiry)->getTable();
        $inquiryObj = Inquiry::from($inquiryTable . ' as i')
                ->join($supplierTable . ' as s', function($join) {
                    $join->on('i.id', '=', 's.inquiry_id');
                })
                ->selectRaw('s.id')
                ->whereRaw('i.end_date < \'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('i.deleted_flag=\'N\'')
                ->whereRaw('s.deleted_flag=\'N\'')
                ->whereRaw('s.supplier_biz_status=\'A\'')
                ->get();
        if (empty($inquiryObj)) {
            return;
        }
        $inquiryList = $inquiryObj->toArray();
        foreach ($inquiryList as $inquiry) {
            Supplier::where('id', $inquiry['id'])->update(['supplier_biz_status' => 'D',
                'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

}
