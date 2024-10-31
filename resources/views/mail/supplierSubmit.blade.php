<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>【{{env('APP_NAME')}}】供应商已提交企业认证信息，请尽快登录系统审核。</title>
    </head>
    <body>

        <p>您好，【{{$name}}】已提交企业认证信息，请尽快登录系统完成认证审核。<a href="{{env('BOSS_URL')}}/front/#/supplierManage/supplierDetail?id={{$supplier_id}}&dataType=audit" target="_blank">快速处理</a></p>
        <p></p>
        <p>瑞招采服务平台</p>
    </body>
</html>
