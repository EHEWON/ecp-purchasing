<?php

$app = require __DIR__.'/../../bootstrap/app.php';

$errorMessage="";
$routes = getRoutes();
$theme = getTheme();
$env = (PHP_SAPI == 'cli') ? TRUE : FALSE;


function getTheme(){
    // 主题风格，fold = 折叠，expand = 展开
    $theme = isset($_GET['type']) ? $_GET['type'] : 'fold';
    return $theme;
}

function getRoutes(){
    $routes = [];
    foreach(app()->router->getRoutes() as $key=>$val){
        $k = $val['uri'];
        $routes[$k]['method'] = $val['method'];
        $routes[$k]['uri'] = $val['uri'];
        $routes[$k]['link']= null;
        $routes[$k]['controller'] = null;
        $routes[$k]['action'] = null;
        $routes[$k]['as'] = null;
        $routes[$k]['desc'] = '//请使用@desc 注释';
        if(isset($val['action']['uses'])){
            list($controller,$action) =explode('@',$val['action']['uses']);
            $routes[$k]['controller'] = $controller;
            $routes[$k]['action'] = $action;
            $routes[$k]['desc'] = getActionDesc($controller,$action);
            $routes[$k]['link'] = 'detail.php?uri='.$val['uri'];
        }
        if(isset($val['action']['as'])){
            $routes[$k]['as'] = $val['action']['as'];
        }
    }
    return array_values($routes);
}

function getActionDesc($controller,$action){
    $rMethod = new Reflectionmethod($controller, $action);
    $desc       = '//请使用@desc 注释';
    $docComment = $rMethod->getDocComment();
    if ($docComment !== false) {
        $docCommentArr = explode("\n", $docComment);
        foreach ($docCommentArr as $comment) {
            $pos = stripos($comment, '@desc');
            if ($pos !== false) {
                $desc = substr($comment, $pos + 5);
            }
        }

    }
    return $desc;
}

ob_start ();
include dirname(__FILE__) . '/list_tpl.php';
