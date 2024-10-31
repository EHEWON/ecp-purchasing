<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>【{{env('APP_NAME')}}】恭喜你！成功入围发布【{{$name}}】的招标项目。</title>
    </head>
    <body>
        <p>您好，发布【{{$name}}】的招标项目，您已成功入围，请做好投标准备。<a href="{{env('BOSS_URL')}}/front/#/supplierTenders/entryDetails?id={{$projectId}}" target="_blank">快速处理</a></p>
        <p>瑞招采服务平台</p>
    </body>
</html>
