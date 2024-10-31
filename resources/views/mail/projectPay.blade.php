<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>您参与的招标项目的【{{$name}}】的缴费已确认，请尽快登录系统完成标书下载和投标！</title>
    </head>
    <body>
        <p>您好，您参与的招标项目的【{{$name}}】的缴费已确认，请尽快登录系统完成标书下载和投标。<a href="{{env('BOSS_URL')}}/front/#/#/supplierTenders/bidDetails?id={{$projectId}}" target="_blank">快速处理</a>。</p>
        <p>瑞招采服务平台</p>
    </body>
</html>
