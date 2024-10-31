<?php

namespace App\Common\Validation;

use Illuminate\Support\Facades\Log;

class Upload {

    public $path = "./Data/Store/";    //上传文件保存的路径
    public $allowtype = array('jpg', 'jpeg', 'gif', 'png');  //设置限制上传文件的类型
    public $maxsize = 2097152;    //设置限制上传图片大小
    public $errorMess = "";       //错误信息
    public $israndname = true;              //设置是否随机重命名文件， false不随机
    public $originName = '';                 //源文件名
    public $tmpFileName = '';                 //临时文件名
    public $fileType = '';                  //文件类型(文件后缀)
    public $fileSize = '';                  //文件大小
    public $fileName = array();                 //自定义文件名,按照上传顺序重命名

    public function upload($files = array()) {


        if (!$_FILES) {
            return "上传文件不能为空";
        }
        $dir = $this->path;
        //检查目标文件
        $this->checkFileExists($dir);

        // 获取文件上传的信息 多文件OR单文件
        $files = $this->getFiles($files);
        $error = '';
        Log::info('↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑' . json_encode($files));

        foreach ($files as $key => $fileInfo) {
//            Log::info('↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑'.json_encode($fileInfo['name']).$key);
            if ($fileInfo['name']) {
                $ret[$key] = $this->uploadFile($fileInfo, $dir, $key);
            }
        }

        $this->imagePngSizeAdd($ret);

        return $this->imgHandle($ret);
    }

    public function getFiles($fileArr = array()) {

        if (!$fileArr)
            $fileArr = $_FILES;
        Log::info(json_encode($fileArr));
        $files = array();

        foreach ($fileArr as $file) {

            Log::info('-------字符串--------');

            if (is_array($file['name'])) {
                Log::info('------------------------' . json_encode($file['name']));
                $fileNum = count($file['name']);
                Log::info('------------------------' . $fileNum);
                for ($i = 0; $i < $fileNum; $i++) {
                    $files[$i]['name'] = $file['name'][$i];
                    $files[$i]['type'] = $file['type'][$i];
                    $files[$i]['tmp_name'] = $file['tmp_name'][$i];
                    $files[$i]['error'] = $file['error'][$i];
                    $files[$i]['size'] = $file['size'][$i];
                }
                return $files;
            }

            $files[$i]['name'] = $file['name'][$i];
            $files[$i]['type'] = $file['type'][$i];
            $files[$i]['tmp_name'] = $file['tmp_name'][$i];
            $files[$i]['error'] = $file['error'][$i];
            $files[$i]['size'] = $file['size'][$i];
        }
        return $files;
    }

    public function checkFile($files) {
        //检查上传方式 POSt
        if (!$this->checkHttpType($this->tmpFileName)) {
            return $this->getError(1);
        }

        //检查文件类型
        if (!$this->checkFileType($this->fileType)) {
            return $this->getError(2);
        }

        //检查文件大小
        if (!$this->checkFileSize($this->fileSize)) {
            return $this->getError(3);
        }
    }

    // 检查HTTP上传方式 POST
    public function checkHttpType($tmp_name) {
        if (is_uploaded_file($tmp_name)) {
            return true;
        }
    }

    //检查上传的目标文件是否存在
    public function checkFileExists($dir) {
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    //检查文件大小
    public function checkFileSize($file_size) {
        if ($this->maxsize > $file_size) {
            return true;
        }
    }

    //检查文件类型
    public function checkFileType($file_type) {
        if (in_array(strtolower($file_type), $this->allowtype)) {
            return true;
        }
    }

    //获取后缀,根据后缀,判断文件类型
    public function getExt($filename) {
        $tmp = explode('.', $filename);
        return end($tmp);
    }

    //出错信息
    public function getError($type) {
        $str = $this->originName;
        switch ($type) {
            case 1 : $str .= "HTTP 上传方式错误";
                break;
            case 2 : $str .= "上传文件格式错误";
                break;
            case 3 : $str .= "上传文件过大";
                break;
            case 4 : $str .= "上传文件失败";
                break;
        }
        return $str;
    }

    //生成随机文件名
    public function randName($length = 6) {
        $str = 'abcdefghigklmbopquvwxyz1234567890';
        return substr(str_shuffle($str), 0, $length);
    }

    //文件上传
    public function uploadFile($files, $dir, $key = '') {
        $ext = $this->getExt($files['name']);
        if ($this->israndname) {
            $filename = date('YmdHis') . $this->randName() . '.' . $ext;
        } else {
            if (!$this->fileName) {
                $filename = iconv("UTF-8", "GB2312", $files['name']);
            } else {
                $filename = $this->fileName[$key] . '.' . $ext;
            }
        }

        if (!move_uploaded_file($files['tmp_name'], $dir . $filename)) {
            return $this->getError(4);
        }
        return $dir . $filename;
    }

    public function imgHandle($paths) {

        foreach ($paths as $k => $v) {
            list($width, $height, $type) = getimagesize($v);
            $arr['path'] = $paths;
            $arr['height'][] = $height;
        }
        return $arr;
    }

    //图片高清压缩
    public function imagePngSizeAdd($src) {
        foreach ($src as $value) {

            $imgwidth = "750";
            $percent = 0.7;  #原图压缩，不缩放

            $imgsrc = $value;
            $imgdst = $value;
            $size = (int) (filesize($imgdst) / 1024);

            list($width, $height, $type) = getimagesize($imgsrc);
            if ($width > 750 || $size > 70 || $width < 750) {
                $image = new ImgCompress($imgsrc, $percent, $imgwidth);
                $image->compressImg($imgdst);
            }
        }
    }

}
