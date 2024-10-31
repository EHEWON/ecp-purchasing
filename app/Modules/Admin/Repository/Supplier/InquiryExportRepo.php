<?php

namespace App\Modules\Admin\Repository\Supplier;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Inquiry,
    Inquiry\Supplier,
    UserSupplier
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    Inquiry\EntryRepo,
    Inquiry\InquiryRepo AS IInquiryRepo,
    OrgRepo,
    PaycondRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo,
    Supplier\InquiryRepo,
    UserRepo
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class InquiryExportRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'title',
        'biz_status',
        'bill_status',
        'bill_date',
        'end_date',
        'person_id',
    ];

    public function __construct() {
        $this->model = new Inquiry();
        parent::__construct($this->model);
    }

    /**
     * 对应表
     *
     */
    private function _getKeys() {
        return [
            ['bill_no', '询价单号', ''],
            ['title', '询价标题', ''],
            ['bill_status_name', '单据状态', ''],
            ['biz_status_name', '项目状态', ''],
            ['bill_date', '业务日期', ''],
            ['end_date', '报价截止日期', ''],
//            ['turns_name', '轮次', ''],
            ['sup_scope_name', '询价范围', ''],
            ['open_type_name', '开标方式', ''],
            ['person_name', '采购员', ''],
            ['phone', '联系电话', ''],
//            ['related_no', '关联单号', ''],
            ['total_inquiry', '整单询价', ''],
            ['remark', '备注', ''],
            ['deli_date', '交货日期', ''],
            ['date_from', '价格有效期从', ''],
            ['date_to', '价格有效期至', ''],
            ['settle_type_name', '结算方式', ''],
            ['paycond_name', '付款条件', ''],
            ['tax_cal_type_name', '计税类型', ''],
            ['curr_name', '币种', ''],
            ['inv_type_name', '发票类型', ''],
            ['stock_code', '存货编码', ''],
            ['material_name', '物料名称', ''],
//            ['specification_model', '规格型号', ''],
            ['material_desc', '物料描述', ''],
            ['inquire_qty', '询价数量', '#,##0.0000'],
            ['inquiry_unit_id_name', '询价单位', ''],
        ];
    }

    private function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

    protected $objPHPExcel = null;
    protected $objSheet = null;

    function xlsStart($keys, $tmpDir, $name, $i) {
        $config = [
            'path' => $tmpDir
        ];
        if (!empty($this->objPHPExcel)) {
            return;
        }
        $this->objPHPExcel = new \Vtiful\Kernel\Excel($config);
        $this->objSheet = $this->objPHPExcel->fileName($name . date('YmdHi') . '_' . $i . '.xlsx', '询报价单表');
        $headers = [];
        foreach ($keys as $key) {
            $headers[] = $key[1];
        }
        $fileHandle = $this->objPHPExcel->getHandle();
        $format = new \Vtiful\Kernel\Format($fileHandle);
        $this->boldStyle = $format->border(\Vtiful\Kernel\Format::BORDER_THIN)
                ->fontSize(10)
                ->font('宋体')
                ->align(\Vtiful\Kernel\Format::FORMAT_ALIGN_CENTER, \Vtiful\Kernel\Format::FORMAT_ALIGN_VERTICAL_CENTER)
                ->wrap()
                ->toResource();
        $this->objSheet->header($headers);
    }

    function xlsend($j) {
        $this->objSheet->freezePanes(1, 1);
        $this
                ->objSheet
                ->setColumn('A0:BZ' . ($j + 1), 15.13)
                ->setColumn('B0:B' . ($j + 1), 20)
                ->setColumn('AB0:AR' . ($j + 1), 20)
                ->setRow('A0:BZ' . ($j + 1), 20);
        $this->objSheet->output();
        $this->objPHPExcel = null;
        $this->objSheet = null;
    }

    public function formatInsertText($k, $rowname, $val, $format) {
        switch ($format) {
            case '#,##0.0000': $this->objSheet
                        ->insertText($k + 1, $rowname, floatval($val), $format, $this->boldStyle);
                break;
            case '#,##0': $this->objSheet
                        ->insertText($k + 1, $rowname, intval($val), $format, $this->boldStyle);
                break;
            default : $this->objSheet
                        ->insertText($k + 1, $rowname, $val, $format, $this->boldStyle);
                break;
        }
    }

    /**
     * 导出
     * @param $request
     * @return array
     */
    public function export(Request $request) {
        $admin = Auth::guard('admin')->user();
        if (empty($admin->user_type) || $admin->user_type !== 'SUPPLIER') {
            check(false, '您不是供应商用户');
        }
        $userId = $admin->user_id;
        $supplierId = UserSupplier::where('user_id', $userId)
                ->where('deleted_flag', 'N')
                ->value('supplier_id');
        if (empty($supplierId)) {
            check(false, '您没有关联供应商');
        }
        $inquiryRepo = (new InquiryRepo);
        $supplierTable = (new Supplier)->getTable();
        $supplierQuery = Supplier::from($supplierTable . ' as sup')
                ->selectRaw('sup.inquiry_id,max(sup.entry_turns) as max_turns')
                ->where('sup.deleted_flag', 'N')
                ->where('sup.supplier_id', $supplierId)
                ->orderBy('sup.entry_turns', 'DESC')
                ->groupBy('sup.inquiry_id');

        $query = $this->model
                ->selectRaw('i.*,is.supplier_id,is.quote_date,is.quote_id,is.quote_id,entry_status,'
                        . 'is.supplier_biz_status,is.entry_turns,is.entry_count,is.contact_name,is.contact_phone,'
                        . 'is.contact_email')
                ->from($this->model->getTable() . ' as i')
                ->leftJoinSub($supplierQuery, 'max', function ($join) {
                    $join->on('i.id', '=', 'max.inquiry_id');
                })
                ->leftJoin($supplierTable . ' as is', function($join)use($supplierId) {
            $join->on('i.id', '=', 'is.inquiry_id')
            ->on('is.entry_turns', 'max.max_turns')
            ->where('is.supplier_id', $supplierId)
            ->where('is.deleted_flag', 'N');
        });
        $query->where(function($q) {
                    $q->whereRaw('is.id IS NOT NULL')
                    ->where('i.sup_scope', '2')
                    ->orWhere(function($q1) {
                        $q1->where('i.sup_scope', 1);
                    });
                })
                ->where('i.bill_status', 'C');
        if ($request->type === 'ALL') {
            $query->where('i.deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('i.deleted_flag', 'N')
                    ->whereIn('i.id', $request->ids);
        } else {
            $inquiryRepo->getWhere($query, $request);
        }
        $clone = $query->clone();
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $count = $clone->count();
        $name = '导出的询报价单';
        $ds = DIRECTORY_SEPARATOR;
        $relativeDir = $ds . 'download' . $ds . date('Ymd') . $ds . uniqid() . $ds;
        $tmpDir = base_path() . $ds . 'public' . $relativeDir;
        $this->RecursiveMkdir($tmpDir);
        $keys = $this->_getKeys();
        $i = 1;
        $k = 0;
        $pk = 1;
        $pagesize = 250;
        $curRepo = (new CurrencyRepo);
        $payRepo = (new PaycondRepo);
        $setRepo = (new SettleMentTypeRepo);
        $orgRepo = (new OrgRepo);
        $userRepo = (new UserRepo);
        $supRepo = (new SupplierBaseRepo);
        $entryRepo = (new EntryRepo);
        $rfq = new IInquiryRepo();
        for ($start = 0; $start <= $count; $start += $pagesize) {
            $clone1 = $query->clone();
            $clone1->orderBy('bill_date', 'DESC');
            $clone1->offset($start)->limit(250);
            $object = $clone1->get();
            unset($clone1);
            if (empty($object)) {
                return [];
            }
            $data = $object->toArray();
            foreach ($data as &$item) {
                $item['bill_status_name'] = $rfq->getBillStatusText($item['bill_status']);
                $item['biz_status_name'] = $rfq->getBizStatusText($item['biz_status']);
                $item['sup_scope_name'] = $rfq->getSupScopeText($item['sup_scope']);
                $item['total_inquiry'] = $item['total_inquiry'] == '1' ? '是' : '否';
                $item['open_type_name'] = $rfq->getOpenTypeText($item['open_type']);
                $item['tax_cal_type_name'] = $rfq->getTaxCalTypeText($item['tax_cal_type']);
                $item['turns_name'] = $rfq->getTurnsText($item['turns']);
                $item['inv_type_name'] = $rfq->getInvtypeText($item['inv_type']);
            }
            $curRepo->setCurrencys($data, 'curr_id', ['curr_name' => 'name']);
            $payRepo->setPayconds($data, 'payment_terms');
            $setRepo->setSettleMentTypes($data, 'settle_type_id', 'settle_type_name');
            $userRepo->setUsers($data, 'person_id', 'person_name');
            $entryRepo->setEntrys($data);
            $maxSupplier = 0;
            $supRepo->setSuppliers($data, $maxSupplier);
            if ($maxSupplier > 0) {
                for ($ms = 0; $ms < $maxSupplier; $ms++) {
                    $keys[] = ['supplier_name' . '_' . $ms, '供应商' . ($ms + 1), ''];
                }
            }
            if (empty($this->objPHPExcel)) {
                $this->xlsStart($keys, $tmpDir, $name, $i);
            }
            foreach ($data as $val) {
                if ($k > 10000) {
                    $this->xlsend($k);
                    $i++;
                    $k = 0;
                    $this->xlsStart($keys, $tmpDir, $name, $i);
                }

                $this->InsertCell($keys, $k, $pk, $val);
            }
        }
        $this->xlsend($k);
        $realFiles = [];
        $files = scandir($tmpDir);
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                $realFiles[] = $item;
            }
        }
        if (count($realFiles) == 1) {
            $url = env('APP_URL') . str_replace($ds, '/', $relativeDir) . $realFiles[0];
            return ['file_url' => $url, 'attach_name' => $realFiles[0]];
        }
        $this->xlsend($k);
        $zip = new ZipArchive();
        $name = '询价单_' . date("YmdHis", time());
        $zipFile = $name . '.zip';
        $relzipDir = $ds . 'download' . $ds . date('Ymd') . $ds;
        $filedir = base_path() . $ds . 'public' . $relzipDir;
        @mkdir($filedir, 0777, true);
        $filepath = dirname($tmpDir) . '/' . $zipFile;
        $res = $zip->open($filepath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            return false;
        }

        foreach ($realFiles as $item) {
            $zip->addFile($tmpDir . $item, $item);
        }
        $zip->close();
//清理临时目录
        foreach ($realFiles as $item) {
            unlink($tmpDir . $item);
        }
        rmdir($tmpDir);
        $url = env('APP_URL') . str_replace($ds, '/', $relzipDir) . $zipFile;
        return ['file_url' => $url, 'attach_name' => $zipFile];
    }

    private function InsertCell($keys, &$k, &$pk, $item) {
        if (!empty($item['entrys'])) {
            $this->setItemCell($item['entrys'], $keys, $k, $item);
            return;
        }
        foreach ($keys as $rowname => $key) {
            if ($key && isset($item)) {
                $value = isset($item[$key[0]]) ? $item[$key[0]] : ' ';
                strpos($value, '=') === 0 ? $value = '\'' . $value : ' ';
                $format = isset($key[2]) ? $key[2] : '';
                $this->formatInsertText($k, $rowname, $value, $format);
            } else {
                $this->objSheet
                        ->insertText($k + 1, $rowname, ' ', '', $this->boldStyle);
            }
        }
        $k++;
        $pk++;
    }

    /**
     *
     * @param type $items
     * @param type $keys
     * @param type $k
     * @param string $item
     */
    private function setItemCell($items, $keys, &$k, $item) {

        foreach ($items as $val) {
            $excel_key = 0;
            $val = array_merge($val, $item);
            foreach ($keys as $rowname => $key) {
                $format = isset($key[2]) ? $key[2] : '';
                if ($key && !empty($val[$key[0]])) {
                    strpos($val[$key[0]], '=') === 0 ? $val[$key[0]] = '\'' . $val[$key[0]] : '';
                    $this->formatInsertText($k, $rowname, $val[$key[0]], $format);
                } else {
                    $this->objSheet->insertText($k + 1, $rowname, ' ', '', $this->boldStyle);
                }
                $excel_key++;
            }
            $k++;
        }
    }

}
