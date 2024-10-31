<!--采购项目列表-->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>采购项目</title>
        <meta id="meta_description" name="description" content="" />
        <meta name="keywords" content="" />
        <link rel="icon" href="/front/EHAdmin.ico">
        <link rel="stylesheet" href="/static/ecp_index/bootstrap.css?v=1.0.1">
        <link rel="stylesheet" type="text/css" href="/static/ecp_index/layui2.6.8/css/layui.css"/>
        <link rel="stylesheet" href="/static/ecp_index/index.css">
        <link rel="stylesheet" href="/static/ecp_index/footer.css">
        <link rel="stylesheet" href="/static/ecp_index/list.css">
        <script src="/static/ecp_index/jquery.min.js"></script>
    </head>

    <body class="body_container">
        <div class="container_box">
            @include('tpl.search', [])
            <div class="container">
                <div class="ecp_nav_box">
                    <a href="/">首页</a><i>/</i>
                    <span>采购项目</span>
                </div>
                <div class="content_box ajax_result_box">
                    @include('tpl.list_ajax', ['pager'=>$pager,
                    'request'=>$request,
                    'data'=>$data,
                    'pagesize'=>$pagesize,
                    ])
                </div>
            </div>
            @include('tpl.footer', [])
        </div>
    </div>
    <script src="/static/ecp_index/layui2.6.8/layui.js"></script>
    <script src="/static/ecp_index/list.js"></script>
    <script type="text/javascript">
        let pagesize = "{{$pagesize}}"
    </script>
</body>
</html>