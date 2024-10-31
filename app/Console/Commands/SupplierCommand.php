<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\Models\{
    UserSupplier,
    Supplier
};

// 后台用户数据导入
class SupplierCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier:deduplication';

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
        try {
            $list = Supplier::selectRaw('`name`,GROUP_CONCAT(id) AS supplier_ids,count(id ),GROUP_CONCAT(purchaser_id)')
                    ->whereRaw('`status`=\'APPROVED\'')
                    ->where('deleted_flag', 'N')
                    ->groupBy('name')
                    ->havingRaw('count(id)>1')
                    ->orderBy('purchaser_id', 'ASC')
                    ->get()
                    ->toArray();
            foreach ($list AS $item) {
                $supplierIds = explode(',', $item['supplier_ids']);
                $userSuppliers = UserSupplier::whereIn('supplier_id', $supplierIds)->get()->toArray();
                $suppliers = Supplier::whereIn('id', $supplierIds)->get()->toArray();
                $supplierArr = array_column($suppliers, 'id', 'purchaser_id');
                if (count($userSuppliers) === 1) {
                    foreach ($userSuppliers as $userSupplier) {
                        $purchaserId = $supplierArr[$supplierArr[$userSupplier['supplier_id']]];
                    }
                }
            }
        } catch (Exception $ex) {
            Illuminate\Support\Facades\Log::channel('command')->info(__CLASS__ . $ex->getMessage());
        }
    }

}
