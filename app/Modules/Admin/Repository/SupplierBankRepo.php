<?php

namespace App\Modules\Admin\Repository;

use App\Common\Contracts\Repository;
use App\Common\Models\SupplierBank;
use Illuminate\Http\Request;

class SupplierBankRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new SupplierBank();
        parent::__construct($this->model);
    }

    public function getList(int $supplierId) {
        if (empty($supplierId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('supplier_id', $supplierId);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        (new CurrencyRepo)->setCurrencys($list);
        return $list;
    }

    public function updateData(int $supplierId, Request $request) {
        SupplierBank::where('supplier_id', $supplierId)->delete();
        $bankList = $this->getBanks($supplierId, $request);
        if (!empty($bankList)) {
            SupplierBank::insert($bankList);
        }
    }

    public function getBanks(int $supplierId, Request $request) {
        $bankList = [];
        if (!empty($request->bank)) {
            foreach ($request->bank as $bank) {
                $bankList[] = [
                    'supplier_id' => $supplierId,
                    'bank_account' => !empty($bank['bank_account']) ? $bank['bank_account'] : '',
                    'name' => !empty($bank['name']) ? $bank['name'] : '',
                    'opening_bank' => !empty($bank['opening_bank']) ? $bank['opening_bank'] : '',
                    'remarks' => !empty($bank['remarks']) ? $bank['remarks'] : '',
                    'currency_id' => !empty($bank['currency_id']) ? intval($bank['currency_id']) : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $bankList;
    }

}
