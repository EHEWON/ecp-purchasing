<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Models\{
    Quote\Quote,
    Inquiry\Inquiry,
    Inquiry\Supplier
};

// 后台用户数据导入
class QuoteCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:operate {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'quote operate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $action = $this->argument('action');
        switch ($action) {
            case 'not_attend':
                try {
                    $this->notAttend();
                } catch (Exception $ex) {
                    Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . $ex->getMessage());
                }
                break;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function notAttend() {
        $this->notAttendQuote();
        $this->notAttendInquiry();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function notAttendQuote() {
        $quoteIds = Quote::whereRaw('bill_status=\'A\'')
                ->whereRaw('end_date < \'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('deleted_flag=\'N\'')
                ->pluck('id');

        if (empty($quoteIds)) {
            return;
        }
        Supplier::whereIn('quote_id', $quoteIds)
                ->whereRaw('deleted_flag=\'N\'')
                ->update(['supplier_biz_status' => 'D',
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function notAttendInquiry() {

        $inquiry = (new Inquiry)->getTable();
        $supplier = (new Supplier)->getTable();
        $supplierIds = Inquiry::from($inquiry . ' as a')
                ->join($supplier . ' as is', function($join) {
                    $join->on('a.id', '=', 'is.inquiry_id');
                })
                ->whereRaw('a.end_date<\'' . date('Y-m-d H:i:s') . '\'')
                ->whereRaw('a.deleted_flag=\'N\'')
                ->whereRaw('`is`.deleted_flag=\'N\'')
                ->whereRaw('`is`.supplier_biz_status=\'A\'')
                ->pluck('is.id');
        if (empty($supplierIds)) {
            return;
        }
        Supplier::whereIn('id', $supplierIds)
                ->whereRaw('deleted_flag=\'N\'')
                ->update(['supplier_biz_status' => 'D',
                    'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

}
