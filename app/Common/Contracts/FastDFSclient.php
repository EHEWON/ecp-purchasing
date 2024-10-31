<?php

namespace App\Common\Contracts;

use CURLFile;

class FastDFSclient {

    //上传附件
    public function uploadAttach($file) {
        $ret = array();

        $ds = DIRECTORY_SEPARATOR;
        $baseDir = dirname(dirname(dirname(__DIR__))) . $ds . 'public' . $ds . 'static' . $ds . 'upload' . $ds;
        $ret['errorcode'] = 0;
        $ret['errormsg'] = '';
        if (empty($file)) {
            $ret['errorcode'] = 1;
            $ret['errormsg'] = "ERROR:upFile is not set";
            return $ret;
        }
        if (false == isset($file['tmp_name']) || false == is_file($file['tmp_name'])) {
            $ret['errorcode'] = 2;
            $ret['errormsg'] = "tmp_name is not file";
            return $ret;
        }
        if (0 == filesize($file['tmp_name'])) {
            $ret['errorcode'] = 3;
            $ret['errormsg'] = "tmp_name filesize is 0";
            return $ret;
        }
        $curlFile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        $fileSuffix = $this->getSuffix($curlFile->getPostFilename());
        $hashUniqid = strtoupper(hash('sha256', uniqid()));
        $startDir = substr($hashUniqid, 0, 2);
        $endDir = substr($hashUniqid, 2, 2);
        $path = $baseDir . $ds . $startDir . $ds . $endDir . $ds;
        $this->RecursiveMkdir($path);
        $filname = strtolower($hashUniqid) . '.' . $fileSuffix;
        move_uploaded_file($file['tmp_name'], $path . $filname);
        $ret['file'] = $file;
        $ret['fileId'] = '/static/upload/' . $startDir . '/' . $endDir . '/' . $filname;
        return $ret;
    }

    //获取后缀
    public function getSuffix($fileName) {
        $matchs = [];
        preg_match('/\.(\w+)?$/', $fileName, $matchs);
        return isset($matchs[1]) ? $matchs[1] : '';
    }

    private function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

}
