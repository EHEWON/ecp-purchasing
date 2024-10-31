<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>【{{env('APP_NAME')}}】【{{$name}}】的评标通知！</title>
    </head>
    <body>
        <p>您好，【{{$name}}】的招标项目已开标，请尽快登录系统完成评标。<a href="{{env('BOSS_URL')}}/front/#/inviteTenders/EvaluationOfBid?id={{$projectId}}" target="_blank">快速处理</a>。</p>
        <p>瑞招采服务平台</p>
    </body>
</html>
