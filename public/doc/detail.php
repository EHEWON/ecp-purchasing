<?php

use Illuminate\Support\Str;

$app = require __DIR__.'/../../bootstrap/app.php';

$route= getRoute();
$rules = getRules($route);
$document = getDocument($route);

function getRoute(){
    $routes = [];
    $uri=$_REQUEST['uri'];
    foreach(app()->router->getRoutes() as $key=>$val){
        $k = $val['uri'];
        $routes[$k]['method'] = $val['method'];
        $routes[$k]['uri'] = $val['uri'];
        $routes[$k]['controller'] = null;
        $routes[$k]['action'] = null;
        $routes[$k]['as'] = null;
        if(isset($val['action']['uses'])){
            list($controller,$action) =explode('@',$val['action']['uses']);
            $routes[$k]['controller'] = $controller;
            $routes[$k]['action'] = $action;
        }
        if(isset($val['action']['as'])){
            $routes[$k]['as'] = $val['action']['as'];
        }
    }
    return isset($routes[$uri])? $routes[$uri] : [];
}

function getRules($route){
    $ret= [];
    $result = [];
    $rules = app()->make($route['controller'],[new Illuminate\Http\Request])->getRules();
    if(isset($rules[$route['action']])){
        $ret=$rules[$route['action']];
        foreach ($ret as $key=>$val){
            $tmpArr = explode("|",$val);
            foreach ($tmpArr as $k =>$v){
                if(Str::contains($v,':'))
                {
                    list($a,$b) = explode(":",$v);
                    $result[$key][$a] = $b;
                }else{
                   $result[$key][$v] = true;
                }
            }
            $result[$key]['name']=$key;
        }
    }
    return $result;
}


function getDocument($route){
    $ret = [
        'returns' => [],
        'description'=>'',
        'descComment'=>'',
        'exceptions' =>''
    ];
    $description=null;
    $rMethod = new Reflectionmethod($route['controller'],$route['action']);
    $docCommentArr = explode("\n", $rMethod->getDocComment());
    foreach ($docCommentArr as $comment) {
        $comment = trim($comment);
        //标题描述
        if (empty($description) && strpos($comment, '@') === FALSE && strpos($comment, '/') === FALSE) {
            $description = substr($comment, strpos($comment, '*') + 1);
            $ret['description']=$description;
            continue;
        }
        //@desc注释
        $pos = stripos($comment, '@desc');
        if ($pos !== FALSE) {
            $descComment = substr($comment, $pos + 5);
            $ret['descComment'] = $descComment;
            continue;
        }

        //@exception注释
        $pos = stripos($comment, '@exception');
        if ($pos !== FALSE) {
            $exArr = explode(' ', trim(substr($comment, $pos + 10)));
            $exceptions[$exArr[0]] = $exArr;
            $ret['exceptions']=$exceptions;
            continue;
        }

        //@return注释
        $pos = stripos($comment, '@return');
        if ($pos === FALSE) {
            continue;
        }

        $returnCommentArr = explode(' ', substr($comment, $pos + 8));
        //将数组中的空值过滤掉，同时将需要展示的值返回
        $returnCommentArr = array_values(array_filter($returnCommentArr));
        if (count($returnCommentArr) < 2) {
            continue;
        }
        if (!isset($returnCommentArr[2])) {
            $returnCommentArr[2] = '';	//可选的字段说明
        } else {
            //兼容处理有空格的注释
            $returnCommentArr[2] = implode(' ', array_slice($returnCommentArr, 2));
        }

        //以返回字段为key，保证覆盖
        $returns[$returnCommentArr[1]] = $returnCommentArr;
        $ret['returns'] = $returns;
    }

    return $ret;

}



ob_start ();
include dirname(__FILE__) . '/detail_tpl.php';
