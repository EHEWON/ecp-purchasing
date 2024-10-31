<?php

namespace App\Modules\Admin\Controller;

use App\Common\Contracts\FastDFSclient;
use Illuminate\Http\Request;
use App\Common\Models\SimpleUpload;
use App\Common\Models\UploadFile AS UploadFileModel;

class UploadFile {

    private $maxsize = 20;           //限制文件上传大小（字节）
    public $tmpDir = '';
    public $saveDir = '';

    public function __construct() {
        $this->tmpDir = app()->basePath() . DIRECTORY_SEPARATOR
                . 'resources' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'simpleUploader';
        $this->saveDir = app()->basePath() . DIRECTORY_SEPARATOR
                . 'resources' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'simpleUploader';
    }

    /**
     * @desc 上传文件
     * @param Request $request 上传文件键值
     * @return array
     */
    public function upload(Request $request) {
        $fileField = 'upFile';
        $file = $_FILES;
        $attachType = $request->get('attach_type', '');
        check(!empty($file), 'Not Found File', 500);

        $fileSize = $file[$fileField]['size'];
        check($fileSize <= intval($this->maxsize) * 1024000, 'The file you uploaded is larger than ' . intval($this->maxsize) . ' M!', 500);
        $size = false;
        $real_name = $file[$fileField]['name'];
        $suffix = $this->getSuffix($file[$fileField]['name']);
        if (in_array(strtolower($suffix), ['jpg', 'png', 'jpeg', 'gif', 'bmp'])) {
            $size = getimagesize($file[$fileField]['tmp_name']);
        }


//上传到fastDFS
        try {
            $fastdfs = new FastDFSclient();
            $ret = $fastdfs->uploadAttach($file[$fileField], 'group1');
            check(!empty($ret['fileId']), !empty($ret['errormsg']) ? $ret['errormsg'] : 'Failed', 500);
            $return = [
                'code' => 200,
                'url' => config('upload.host') . '/' . trim($ret['fileId'], '/'),
                'name' => $ret['file']['name'],
                'size' => $file[$fileField]['size'],
                'real_name' => $real_name,
            ];
            if ($size) {
                $return['width'] = $size[0];
                $return['width'] = $size[1];
            }
            UploadFileModel::insert(
                    [
                        'file_url' => $return['url'],
                        'file_name' => $ret['file']['name'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]
            );
            return $return;
        } catch (Exception $ex) {
            check(false, $ex->getMessage(), $ex->getCode());
        }
    }

    //获取后缀
    public function getSuffix($fileName) {
        $matchs = [];
        preg_match('/\.(\w+)?$/', $fileName, $matchs);
        return isset($matchs[1]) ? $matchs[1] : '';
    }

    /**
     * 图片旋转
     */
    public function rotate($filename) {
        $exif = exif_read_data($filename);
        if ($exif['Orientation'] === 1) {
            return $filename;
        }
        switch ($exif['Orientation']) {
            case 6:
                $degrees = -90;
                break;
            case 3:
                $degrees = 180;
                break;
            case 8:
                $degrees = 90;
                break;
        }
        $path = MYPATH . DS . 'public' . DS . 'tmp';
        $destFile = $path . DS . md5($filename) . '.jpg';
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
        $source = imageCreateFromJpeg($filename);
//使用imagerotate()函数按指定的角度旋转
        $rotate = imagerotate($source, $degrees, 0);
        imageJpeg($rotate, $destFile, '85');
        imagedestroy($source);
        imagedestroy($rotate);
        return $destFile;
    }

    public function kindeditor() {
        header('Content-Type:application/json; charset=utf-8');
        header('P3P:CP=\'IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\'');
        header('X-Frame-Options:*');
        $file = $_FILES;
        $max_size = 1048576;
//检查文件大小
        if (!empty($file['imgFile']['error'])) {
            switch ($file['imgFile']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }
        $file_name = $file['imgFile']['name'];
//服务器上临时文件名
        $tmp_name = $file['imgFile']['tmp_name'];
//文件大小
        $file_size = $file['imgFile']['size'];
        if (!$file_name) {
            $this->alert("请选择文件。");
        }
        if (@is_uploaded_file($tmp_name) === false) {
            $this->alert("上传失败。");
        }
        if ($file_size > $max_size) {
            $this->alert("您上传的文件大于1M。");
        }

        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file['imgFile']);

        if (!empty($ret['fileId'])) {
            $fastDFSUrl = config('upload.host') . '/' . trim($ret['fileId'], '/');

            header('Content-Type:application/json; charset=utf-8');
            header('P3P:CP=CAO PSA OUR');
            echo json_encode(array('error' => 0, 'url' => $fastDFSUrl));
            exit;
        } else {
            $this->alert("上传文件失败。");
        }
        exit;
    }

    function alert($msg) {
        header('Content-Type:application/json; charset=utf-8');
        header('P3P:CP=CAO PSA OUR');
        echo json_encode(array('error' => 1, 'message' => $msg));
        exit;
    }

    public $fileInfo = [
        'identifier' => '', //文件的唯一标识
        'chunknumber' => 1, //当前是第几个分片
        'totalchunks' => 1, //总分片数
        'filename' => '', //文件名称
        'totalsize' => 0  //文件总大小
    ];

//检测断点和md5
    public function checkFile(Request $request) {
        $this->check(!empty($request['identifier']), '唯一标识不能为空');
        $this->check(!empty($request['totalSize']), '文件总大小不能为空');
        $this->check(!empty($request['totalChunks']), '总分片数不能为空');
        $this->fileInfo = [
            'identifier' => htmlentities($request['identifier']), //每个文件的唯一标识
            'filename' => !empty($request['filename']) ? htmlentities($request['filename']) : null, //文件名称
            'totalsize' => intval($request['totalSize']), //文件总大小
            'chunknumber' => !empty($request['chunknumber']) ? htmlentities($request['chunknumber']) : null,
            'totalchunks' => intval($request['totalChunks']) //总分片数
        ];
        $fileInfo = UploadFileModel::where('identifier', $this->fileInfo['identifier'])
                ->where('file_name', $this->fileInfo['filename'])
                ->first();
        if (!empty($fileInfo)) {
            $this->message(0, '上传成功。', ['url' => $fileInfo->file_url,
                'merge' => false,
                'file_name' => $fileInfo->file_name,
                'time' => 0,
            ]);
        }
        $identifier = $this->fileInfo['identifier'];
        $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $identifier; //临时分片文件路径
        $totalChunks = $this->fileInfo['totalchunks'];
        //检测文件md5是否已经存在
        $rs = $this->checkMd5($identifier, $this->fileInfo['totalsize']);
        if ($rs['isExist'] === true) {
            $this->check(false, '已上传');
        }

        //检查分片是否存在
        $chunkExists = [];
        for ($index = 1; $index <= $totalChunks; $index++) {
            if (file_exists("{$filePath}_{$index}")) {
                array_push($chunkExists, $index);
            }
        }
        if (count($chunkExists) == $totalChunks) { //全部分片存在，则直接合成
            $this->fileInfo['filename'] = SimpleUpload::selectRaw('count(*) as t,filepath')
                    ->where('identifier', $identifier)
                    ->where('totalsize', $this->fileInfo['totalsize'])
                    ->value('filename');
            $this->merge();
        } else {
            $this->message(1001, '可以上传', ['uploaded' => $chunkExists]);
        }
    }

//检测md5表是否已存在该文件
    private function checkMd5($md5, $filesize) {
        $row = SimpleUpload::selectRaw('count(*) as t,filepath')
                ->where('identifier', $md5)
                ->where('totalsize', $filesize)
                ->first();
        $count = $row->t;
        if ($count > 0) {
            $res['isExist'] = true;
            $res['filepath'] = $row->filepath;
        } else {
            $res['isExist'] = false;
        }
    }

    //上传分片
    public function simpleUploader(Request $request) {
        $this->check(!empty($request['identifier']), '唯一标识不能为空');
        $this->check(!empty($request['totalSize']), '文件总大小不能为空');
        $this->check(!empty($request['totalChunks']), '总分片数不能为空');
        $this->check(!empty($request['chunkNumber']), '当前是第几个分片不能为空');
        $this->check(!empty($request['filename']), '文件名称不能为空');
        $this->fileInfo = [
            'identifier' => htmlentities($request['identifier']), //每个文件的唯一标识
            'filename' => htmlentities($request['filename']), //文件名称
            'totalsize' => intval($request['totalSize']), //文件总大小
            'chunknumber' => intval($request['chunkNumber']), //当前是第几个分片
            'totalchunks' => intval($request['totalChunks']) //总分片数
        ];
        $fileInfo = UploadFileModel::where('identifier', $this->fileInfo['identifier'])
                ->where('file_name', $this->fileInfo['filename'])
                ->first();
        if (!empty($fileInfo)) {
            $this->message(0, '上传成功。', ['url' => $fileInfo->file_url,
                'merge' => false,
                'file_name' => $fileInfo->file_name,
                'time' => 0,
            ]);
        }
        $isExist = SimpleUpload::selectRaw('count(*) as t,filepath')
                ->where('identifier', $this->fileInfo['identifier'])
                ->where('totalsize', $this->fileInfo['totalsize'])
                ->where('chunknumber', $this->fileInfo['chunknumber'])
                ->count();
        if ($isExist) {
            $this->check(false, '已上传');
        }
        if (!empty($_FILES)) {
            $in = @fopen($_FILES["file"]["tmp_name"], "rb");
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                $this->check(false, '打开临时文件失败');
            }
        } elseif (!$in = @fopen("php://input", "rb")) {
            $this->check(false, '打开输入流失败');
        }
        if ($this->fileInfo['totalchunks'] === 1) {
            @fclose($in);
            $file['tmp_name'] = $_FILES['file']['tmp_name'];
            $file['name'] = $this->fileInfo['filename'];
            $file['type'] = $_FILES['file']['type'];
            $file['size'] = $this->fileInfo['totalsize'];
            $fastdfs = new FastDFSclient();
            $ret = $fastdfs->uploadAttach($file);
            if (!empty($ret['fileId'])) {
                $fastDFSUrl = config('upload.host') . '/' . trim($ret['fileId'], '/');
                UploadFileModel::insert(
                        [
                            'file_url' => $fastDFSUrl,
                            'file_name' => $this->fileInfo['filename'],
                            'identifier' => $this->fileInfo['identifier'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]
                );
                $this->message(0, '上传成功。', ['url' => $fastDFSUrl,
                    'merge' => false,
                    'file_name' => $this->fileInfo['filename'],
                    'time' => 0,
                ]);
            } else {
                $this->check(false, '上传文件失败');
            }
        } else { //需要合并
            $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $this->fileInfo['identifier']; //临时分片文件路径
            $uploadPath = $filePath . '_' . $this->fileInfo['chunknumber']; //临时分片文件名
            $merge = true;
        }
        if (!$out = @fopen($uploadPath, "wb")) {
            $this->check(false, '文件不可写');
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($in);
        @fclose($out);
        $data = $this->fileInfo;
        $data['filepath'] = $filePath;
        $data['type'] = $_FILES['file']['type'];
        $data['created_at'] = date('Y-m-d H:i:s');
        SimpleUpload::insert($data);
        return $this->message(1001, '上传成功', ['merge' => !empty($merge) ? $merge : null]);
    }

    //合并文件
    public function merge(Request $request) {
        $this->check(!empty($request['identifier']), '唯一标识不能为空');
        $this->check(!empty($request['totalSize']), '文件总大小不能为空');
        $this->check(!empty($request['totalChunks']), '总分片数不能为空');
        $this->check(!empty($request['filename']), '文件名称不能为空');
        $this->fileInfo = [
            'identifier' => htmlentities($request['identifier']), //每个文件的唯一标识
            'filename' => htmlentities($request['filename']), //文件名称
            'totalsize' => intval($request['totalSize']), //文件总大小
            'chunknumber' => intval($request['chunkNumber']), //当前是第几个分片
            'totalchunks' => intval($request['totalChunks']) //总分片数
        ];
        $fileInfo = UploadFileModel::where('identifier', $this->fileInfo['identifier'])
                ->where('file_name', $this->fileInfo['filename'])
                ->first();
        if (!empty($fileInfo)) {
            $this->message(0, '上传成功。', ['url' => $fileInfo->file_url,
                'merge' => false,
                'file_name' => $fileInfo->file_name,
                'time' => 0,
            ]);
        }
        $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $this->fileInfo['identifier'];
        $totalChunks = $this->fileInfo['totalchunks']; //总分片数
        $filename = $this->fileInfo['filename']; //文件名
        $done = true;
        //检查所有分片是否都存在
        for ($index = 1; $index <= $totalChunks; $index++) {
            if (!file_exists("{$filePath}_{$index}")) {
                $done = false;
                break;
            }
        }
        if ($done === false) {
            $this->check(false, '分片信息错误');
        }
        //如果所有文件分片都上传完毕，开始合并
        $timeStart = $this->getmicrotime(); //合并开始时间
        $saveDir = $this->saveDir . DIRECTORY_SEPARATOR . date('Y-m-d');
        if (!is_dir($saveDir)) {
            @mkdir($saveDir);
        }
        $uploadPath = $saveDir . DIRECTORY_SEPARATOR . $filename;
        if (!$out = @fopen($uploadPath, "wb")) {
            $this->check(false, '文件不可写');
        }
        if (flock($out, LOCK_EX)) { // 进行排他型锁定
            for ($index = 1; $index <= $totalChunks; $index++) {
                if (!$in = @fopen("{$filePath}_{$index}", "rb")) {
                    break;
                }
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
                @fclose($in);
//                @unlink("{$filePath}_{$index}"); //删除分片
            }

            flock($out, LOCK_UN); // 释放锁定
        }
        @fclose($out);
        $timeEnd = $this->getmicrotime(); //合并完成时间
        $file['tmp_name'] = $uploadPath;
        $file['name'] = $filename;
        $file['type'] = $this->getType($uploadPath);
        $file['size'] = $this->fileInfo['totalsize'];
        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file);
        if (!empty($ret['fileId'])) {
            $fastDFSUrl = config('upload.host') . '/' . trim($ret['fileId'], '/');
            UploadFileModel::insert(
                    [
                        'file_url' => $fastDFSUrl,
                        'file_name' => $filename,
                        'identifier' => $this->fileInfo['identifier'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]
            );
            SimpleUpload::selectRaw('count(*) as t,filepath')
                    ->where('identifier', $this->fileInfo['identifier'])
                    ->where('totalsize', $this->fileInfo['totalsize'])
                    ->delete();
            for ($index = 1; $index <= $totalChunks; $index++) {
                @unlink("{$filePath}_{$index}"); //删除分片
            }
            @unlink($uploadPath);
            $this->message(0, '上传成功。', ['url' => $fastDFSUrl,
                'merge' => true,
                'file_name' => $filename,
                'time' => $timeEnd - $timeStart,
            ]);
            exit;
        } else {
            $this->check(false, '上传文件失败');
        }
    }

    private function getType($filename) {
        $finfo = finfo_open(FILEINFO_MIME); // 返回 mime 类型
        $type = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $type;
    }

    //计算时间
    private function getmicrotime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    //返回提示消息
    private function message($code, $message, $data = null) {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(['ret' => 200, 'code' => $code, 'data' => $data, 'message' => $message]);
        die;
    }

    function check(bool $assert, $message, $code = 1) {
        if ($assert) {
            return;
        }
        $this->message($code, $message);
    }

}
