<?php

namespace App\Modules\Admin\Repository\Quote;

use App\Common\Contracts\Repository;
use App\Common\Models\{
    Inquiry\Inquiry,
    Quote\Quote
};
use App\Modules\Admin\Repository\{
    CurrencyRepo,
    Inquiry\InquiryRepo,
    OrgRepo,
    PaycondRepo,
    Quote\EntryRepo,
    SettleMentTypeRepo,
    SupplierBaseRepo,
    UserRepo
};
use Illuminate\Http\Request;
use ZipArchive;

class ExportRepo extends Repository {

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
        $this->model = new Quote();
        parent::__construct($this->model);
    }

    /**
     * 对应表
     *
     */
    private function _getKeys() {
        return [
            ['bill_no', '报价单号', ''],
            ['biz_status_name', '项目状态', ''],
            ['supplier_name', '供应商', ''],
            ['inquiry_title', '询价标题', ''],
            ['bill_date', '报价日期', ''],
            ['end_date', '报价截止日期', ''],
            ['inquiry_no', '询价单号', ''],
            ['delivery_date', '交货期描述', ''],
            ['contact_name', '报价联系人', ''],
            ['contact_phone', '报价方联系方式', ''],
            ['date_from', '价格有效期从', ''],
            ['date_to', '价格有效期至', ''],
            ['settle_type_name', '结算方式', ''],
            ['payment_terms_name', '付款条件', ''],
            ['tax_cal_type_name', '计税类型', ''],
            ['curr_name', '币种', ''],
            ['inv_type_name', '发票类型', ''],
            ['inv_type_name', '发票类型', ''],
            ['material_name', '物料名称', ''],
            ['material_desc', '物料描述', ''],
            ['inquire_qty', '询价数量', '#,##0.0000'],
            ['inquiry_unit_id_name', '询价单位', ''],
            ['qty', '报价数量', '#,##0.0000'],
            ['quote_unit_id_name', '报价单位', ''],
            ['tax_rate', '税率%', '#,##0.0000'],
            ['tax_price', '含税单价', '￥#,##0.0000'],
            ['price', '单价', '￥#,##0.0000'],
            ['tax', '税额', '￥#,##0.00'],
            ['amount', '金额', '￥#,##0.00'],
            ['tax_amount', '价税合计', '#,##0.00'],
            ['warranty_period', '质保期（天）', ''],
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
            case '#,##0.0000':
                $val = !empty(trim($val)) ? trim($val) : 0;
                $this->objSheet
                        ->insertText($k + 1, $rowname, round($val, 4), $format, $this->boldStyle);
                break;
            case '#,##0.00':
                $val = !empty(trim($val)) ? trim($val) : 0;
                $this->objSheet
                        ->insertText($k + 1, $rowname, round($val, 2), $format, $this->boldStyle);
                break;
            case '￥#,##0.00':
                $val = !empty(trim($val)) ? trim($val) : 0;
                $this->objSheet
                        ->insertText($k + 1, $rowname, '￥' . round($val, 2), $format, $this->boldStyle);
                break;
            case '￥#,##0.0000':
                $val = !empty(trim($val)) ? trim($val) : 0;
                $this->objSheet
                        ->insertText($k + 1, $rowname, '￥' . round($val, 4), $format, $this->boldStyle);
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
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $quoteRepo = (new QuoteRepo);
        $inquiryRepo = (new InquiryRepo);
        $filed = 'q.id,q.bill_no,q.org_id,q.inquiry_id,q.turns_count,q.delivery_date,q.date_from,q.date_to,'
                . 'q.inquiry_title,q.bill_date,q.sum_tax_amount,q.biz_status,q.end_date,'
                . 'q.bill_status,q.curr_id,q.supplier_id,q.tax_cal_type,q.contact_name,q.contact_phone,q.contact_email,'
                . 'q.inquiry_no,q.settle_type_id,q.payment_terms,q.inv_type';
        $quote = $this->model->getTable();
        $inquiry = (new Inquiry)->getTable();
        $query = $this->model
                ->from($quote . ' AS q')
                ->join($inquiry . ' AS i', function($join) {
                    $join->on('i.id', '=', 'q.inquiry_id')
                    ->where('i.bill_status', 'C')
                    ->whereIn('i.biz_status', ['B', 'C', 'D', 'E']);
                })
                ->selectRaw($filed);

        $query->where('q.bill_status', 'C');
        if ($request->type === 'ALL') {
            $query->where('q.deleted_flag', 'N');
        } elseif ($request->ids) {
            $query->where('q.deleted_flag', 'N')
                    ->whereIn('q.id', $request->ids);
        } else {
            $quoteRepo->getWhere($query, $request);
        }
        $clone = $query
                ->orderBy('i.bill_date', 'desc')
                ->get();
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $count = $clone->count();
        $name = '导出的报价单';
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
        $userRepo = (new UserRepo);
        $supRepo = (new SupplierBaseRepo);
        $entryRepo = (new EntryRepo);
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
                $item['bill_status_name'] = $quoteRepo->getBillStatusText($item['bill_status']);
                $item['biz_status_name'] = $quoteRepo->getBizStatusText($item['biz_status']);
                $item['tax_cal_type_name'] = $inquiryRepo->getTaxCalTypeText($item['tax_cal_type']);
                $item['inv_type_name'] = $inquiryRepo->getInvtypeText($item['inv_type']);
                $item['turns_count'] = $inquiryRepo->getTurnsText($item['turns_count']);
            }
            $supRepo->setSuppliers($data, 'supplier_id', 'supplier_name');
            $curRepo->setCurrencys($data, 'curr_id', ['curr_name' => 'name']);
            $payRepo->setPayconds($data, 'payment_terms', ['payment_terms_name' => 'name']);
            $setRepo->setSettleMentTypes($data, 'settle_type_id', 'settle_type_name');
            $userRepo->setUsers($data, 'person_id', 'person_name');
            $entryRepo->setEntrys($data);
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
        $files = scandir($tmpDir);
        $realFiles = [];
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                $realFiles[] = $item;
            }
        }

        if (count($realFiles) == 1) {
            $url = env('APP_URL') . str_replace($ds, '/', $relativeDir) . $realFiles[0];
            return ['file_url' => $url, 'attach_name' => $realFiles[0]];
        }
        $zip = new ZipArchive();
        $name = '报价单' . date("YmdHis", time());
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
                $this->objSheet->insertText($k + 1, $rowname, ' ', '', $this->boldStyle);
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
            foreach ($keys as $rowname => $key) {
                $format = isset($key[2]) ? $key[2] : '';
                if ($key && !empty($item[$key[0]])) {
                    strpos($item[$key[0]], '=') === 0 ? $item[$key[0]] = '\'' . $item[$key[0]] : '';
                    $this->formatInsertText($k, $rowname, $item[$key[0]], $format);
                } elseif ($key && !empty($val[$key[0]])) {
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
