<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository;
use App\Common\Models\Project\{
    ProjectBidAttach,
    ProjectBidQuote
};
use App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectBidAttachRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new ProjectBidAttach();
        parent::__construct($this->model);
    }

    public function getList(int $quote_id) {
        if (empty($quote_id)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('quote_id', $quote_id);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
        }
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        return $list;
    }

    public function updateData(int $quote_id, Request $request) {
        ProjectBidAttach::where('quote_id', $quote_id)->delete();
        $attachList = $this->getAttachs($quote_id, $request);
        if (!empty($attachList)) {
            ProjectBidAttach::insert($attachList);
        }
    }

    public function getAttachs(int $quote_id, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'group' => $attach['group'],
                    'quote_id' => $quote_id,
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => $admin->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $attachList;
    }

    public function setAttachNums(&$supplierList, $projectId) {
        $quoteTable = (new ProjectBidQuote)->getTable();
        $attachTable = (new ProjectBidAttach)->getTable();
        $attachObj = ProjectBidAttach::selectRaw('sum( IF(ba.`group` = \'TECHNICAL\', 1, 0) )AS tech_num,'
                        . 'sum( IF(ba.`group` = \'COMMERCIAL\', 1, 0) )AS comm_num,'
                        . 'bq.supplier_id')
                ->from($attachTable . ' as ba')
                ->join($quoteTable . ' as bq', function($join) {
                    $join->on('bq.id', '=', 'ba.quote_id');
                })
                ->where('bq.project_id', $projectId)
                ->whereIn('group', ['TECHNICAL', 'COMMERCIAL'])
                ->groupBy('supplier_id')
                ->get();
        $attachList = !empty($attachObj) ? $attachObj->toArray() : [];
        $attachArr = [];
        foreach ($attachList as $attach) {
            $attachArr[$attach['supplier_id']] = $attach;
        }
        foreach ($supplierList as &$item) {
            $supplierId = $item['supplier_id'];
            $attach = !empty($attachArr[$supplierId]) ? $attachArr[$supplierId] : [];
            $item['tech_num'] = !empty($attach['tech_num']) ? $attach['tech_num'] : '0';
            $item['comm_num'] = !empty($attach['comm_num']) ? $attach['comm_num'] : '0';
        }
    }

    public function download($quotrId, string $group) {
        if (empty($quotrId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('quote_id', $quotrId);
        $qurey->where('group', $group);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        if (empty($list)) {
            return [];
        }
        switch ($group) {
            case 'COMMERCIAL':
                $filename = '商务标文件_' . $quotrId . '.zip';
                break;
            case 'TECHNICAL':
                $filename = '技术标文件_' . $quotrId . '.zip';
                break;
            case 'COMMITMENT_LETTER':
                $filename = '投标承诺函_' . $quotrId . '.zip';
                break;
            case 'OTHER':
                $filename = '其他附件_' . $quotrId . '.zip';
                break;
        }
        $filepath = $this->packAndUpload($filename, $list);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath);
        exit;
    }

    private function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

    /**
     * 打包文件并且上传至FastDFS服务器
     * @param string $filename 压缩包名称
     * @return mixed
     * */
    private function packAndUpload($filename, $list) {
        //创建临时目录
        $ds = DIRECTORY_SEPARATOR;
        $relativeDir = $ds . 'download' . $ds . date('Ymd') . $ds . uniqid() . $ds;
        $tmpDir = base_path() . $ds . 'public' . $relativeDir;
        $this->RecursiveMkdir($tmpDir);
        //复制文件到临时目录
        foreach ($list as $file) {
            $name = $file['attach_name'];
            //如果文件存在则重命名
            if (file_exists($tmpDir . $name)) {
                //循环100次修改文件名
                for ($i = 1; $i < 100; $i++) {
                    $name = preg_replace("/(\.\w+)/i", "($i)$1", $name);
                    if (!file_exists($tmpDir . $name)) {
                        break;
                    }
                }
            }
            //目标文件仍然存在，则写入错误文件
            if (file_exists($tmpDir . $name)) {
                $error_files[] = $file;
            }
            $file_name = iconv('utf-8', 'gbk', $name);
            $content = file_get_contents($file['attach_url']);
            file_put_contents($tmpDir . $file_name, $content);
        }
        //如果有文件无法复制到本目录
        if (!empty($error_files)) {
            return false;
        }
        //生成压缩文件
        $zip = new \ZipArchive();
        $filepath = dirname($tmpDir) . '/' . $filename;
        $res = $zip->open($filepath, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            return false;
        }
        $file_arr = scandir($tmpDir);
        foreach ($file_arr as $item) {
            if ($item != '.' && $item != '..') {
                $zip->addFile($tmpDir . $item, $item);
            }
        }

        $zip->close();
        //清理临时目录
        foreach ($file_arr as $item) {
            if ($item != '.' && $item != '..') {
                unlink($tmpDir . $item);
            }
        }
        rmdir($tmpDir);
        return $filepath;
    }

}
