<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>【{{env('APP_NAME')}}】供应商未竞价通知</title>
    </head>
    <body>
        <p>您好，很遗憾，【{{$name}}】竞价已开启，有供应商未参与竞价，请及时处理。<a href="{{env('BOSS_URL')}}/front/#/bidding/BiddingDetails?id={{$id}}" target="_blank">快速处理</a>。</p>
        <p>瑞招采服务平台</p>
    </body>
</html>
