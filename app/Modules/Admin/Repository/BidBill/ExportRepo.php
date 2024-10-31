<?php

namespace App\Modules\Admin\Repository\BidBill;

use App\Common\Contracts\Repository;
use Illuminate\Http\Request;
use App\Common\Models\BidBill\BidBill;
use App\Modules\Admin\Repository\{
    UserRepo,
    OrgRepo
};
use ZipArchive;

class ExportRepo extends Repository {

    protected $model;
    protected $sorts = [
        'bill_no',
        'name',
        'biz_status',
        'bill_status',
        'org_id',
        'bill_date',
        'enroll_date',
        'open_date',
        'result_date',
        'created_at',
    ];

    public function __construct() {
        $this->model = new BidBill();
        parent::__construct($this->model);
    }

    /**
     * 对应表
     *
     */
    private function _getKeys() {
        return [
            ['seq', '序号', ''],
            ['bill_no', '单据编号', ''],
            ['name', '项目名称', ''],
            ['bill_date', '业务日期', ''],
            ['bid_status_name', '项目状态', ''],
            ['enroll_number', '报名/确认数', '#,##0'],
            ['enroll_date', '报名截止时间', ''],
            ['open_date', '预计竞价开始时间', ''],
            ['result_date', '预计公布结果时间', ''],
            ['sum_tax_amount', '竞价基准金额', '¥#,##0.00'],
            ['bill_status_name', '单据状态', ''],
            ['created_name', '创建人', ''],
            ['created_at', '创建时间', ''],
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
                ->setColumn('B0:N' . ($j + 1), 20);
        $this->objSheet->output();
        $this->objPHPExcel = null;
        $this->objSheet = null;
    }

    public function formatInsertText($k, $rowname, $val, $format) {
        switch ($format) {
            case '#,##0.0000': $this->objSheet
                        ->insertText($k + 1, $rowname, floatval($val), $format, $this->boldStyle);
                break;
            case '¥#,##0.00': $this->objSheet
                        ->insertText($k + 1, $rowname, '¥ ' . number_format($val, 2, '.', ','), '', $this->boldStyle);
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
        $bidbillRepo = (new BidBillRepo);

        $query = $this->model->selectRaw('id,bill_no,'
                . 'name,bill_status,org_id,bill_date,bill_status,bid_status,bid_number,'
                . 'enroll_date,open_date,result_date,sum_tax_amount,created_by,created_at');
        if ($request->ids) {
            $query->whereIn('id', $request->ids);
        } else {
            $bidbillRepo->getWhere($query, $request);
        }
        $clone = $query->clone();
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $count = $clone->count();
        $name = '导出的竞价单';
        $ds = DIRECTORY_SEPARATOR;
        $relativeDir = $ds . 'download' . $ds . date('Ymd') . $ds . uniqid() . $ds;
        $tmpDir = base_path() . $ds . 'public' . $relativeDir;
        $this->RecursiveMkdir($tmpDir);
        $keys = $this->_getKeys();
        $i = 1;
        $k = 0;
        $pk = 1;
        $pagesize = 250;
        $subRepo = (new SubRepo);
        $orgRepo = (new OrgRepo);
        $userRepo = (new UserRepo);
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
                $item['bill_status_name'] = $bidbillRepo->getBillStatusText($item['bill_status']);
                $item['bid_status_name'] = $bidbillRepo->getBidStatusText($item['bid_status']);
            }
            $subRepo->setSubs($data);
            $userRepo->setUsers($data, 'person_id', 'person_name');
            $userRepo->setUsers($data, 'created_by', 'created_name');
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
        $name = '竞价单_' . date("YmdHis", time());
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

}
