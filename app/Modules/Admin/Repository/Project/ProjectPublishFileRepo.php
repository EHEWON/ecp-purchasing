<?php

namespace App\Modules\Admin\Repository\Project;

use App\Common\Contracts\Repository,
    App\Common\Models\Project\Attach,
    App\Modules\Admin\Repository\UserRepo;
use Illuminate\Http\Request,
    Illuminate\Support\Facades\Auth;

class ProjectPublishFileRepo extends Repository {

    protected $model;

    public function __construct() {
        $this->model = new Attach();
        parent::__construct($this->model);
    }

    public function getList(int $projectId) {
        if (empty($projectId)) {
            return [];
        }
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId)
                ->whereIn('group', ['TECHNICAL', 'COMMERCIAL', 'PUBLISH', 'PUBLISH_DOWNLOAD']);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
        $ret = ['technical' => [], 'commercial' => [], 'attachs' => []];
        (new UserRepo)->setUsers($list, 'created_by', 'created_name');
        foreach ($list as &$item) {
            $item['created_at'] = substr($item['created_at'], 0, 10);
            if ($item['group'] === 'PUBLISH') {
                $ret['attachs'][] = $item;
            } else {
                $ret[strtolower($item['group'])][] = $item;
            }
        }
        return $ret;
    }

    public function updateData(int $projectId, Request $request) {
        Attach::where('project_id', $projectId)
                ->whereIn('group', ['TECHNICAL', 'COMMERCIAL', 'PUBLISH'])
                ->delete();
        $attachList = $this->getAttachs($projectId, $request);
        if (!empty($attachList)) {
            Attach::insert($attachList);
        }
        if (!empty($request->publish) && $request->publish['publish_status'] === 'C') {
            $this->packAndUpload($projectId, '标书文件.zip');
        }
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
    private function packAndUpload($projectId, $filename) {
        //创建临时目录
        $ds = DIRECTORY_SEPARATOR;
        $baseDir = base_path() . $ds . 'public' . $ds . 'static' . $ds . 'upload' . $ds;
        $hashUniqid = strtoupper(hash('sha256', uniqid()));
        $startDir = substr($hashUniqid, 0, 2);
        $endDir = substr($hashUniqid, 2, 2);
        $path = $baseDir . $ds . $startDir . $ds . $endDir . $ds;
        $relativeDir = $ds . 'download' . $ds . date('Ymd') . $ds . uniqid() . $ds;
        $this->RecursiveMkdir($path);
        $tmpDir = base_path() . $ds . 'public' . $relativeDir;
        $this->RecursiveMkdir($tmpDir);
        $qurey = $this->model->selectRaw('*');
        $qurey->where('project_id', $projectId)
                ->whereIn('group', ['TECHNICAL', 'COMMERCIAL']);
        $qurey->where('deleted_flag', 'N');
        $object = $qurey->orderBy('id', 'ASC')->get();
        if (empty($object)) {
            return [];
        }
        $list = $object->toArray();
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
        $filname = strtolower($hashUniqid) . '.zip';
        $filepath = $path . $ds . $filname;
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
        $admin = Auth::guard('admin')->user();
        Attach::insert([
            'project_id' => $projectId,
            'group' => 'PUBLISH_DOWNLOAD',
            'attach_name' => $filename,
            'attach_url' => config('upload.host') . '/static/upload/' . $startDir . '/' . $endDir . '/' . $filname,
            'attach_name' => '标书文件.zip',
            'created_by' => $admin->user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getAttachs(int $projectId, Request $request) {
        $attachList = [];
        $admin = Auth::guard('admin')->user();
        if (!empty($request->technical)) {
            foreach ($request->technical as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'project_id' => $projectId,
                    'group' => 'TECHNICAL',
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => !empty($admin) ? $admin->user_id : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        if (!empty($request->commercial)) {
            foreach ($request->commercial as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'project_id' => $projectId,
                    'group' => 'COMMERCIAL',
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => !empty($admin) ? $admin->user_id : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        if (!empty($request->attachs)) {
            foreach ($request->attachs as $attach) {
                if (empty($attach['attach_url']) || $attach['attach_url'] === 'undefined') {
                    continue;
                }
                $attachList[] = [
                    'project_id' => $projectId,
                    'group' => 'PUBLISH',
                    'attach_name' => !empty($attach['attach_name']) ? $attach['attach_name'] : '',
                    'remarks' => !empty($attach['remarks']) ? $attach['remarks'] : '',
                    'attach_url' => !empty($attach['attach_url']) ? $attach['attach_url'] : '',
                    'created_by' => !empty($admin) ? $admin->user_id : null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $attachList;
    }

}
