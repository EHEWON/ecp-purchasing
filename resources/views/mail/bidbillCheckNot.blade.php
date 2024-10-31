<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>【{{env('APP_NAME')}}】【{{$name}}】资审未通过</title>
    </head>
    <body>
        <p>您好，很遗憾，【{{$name}}】资审未通过。。<a href="{{env('BOSS_URL')}}/front/#/biddingManage/biddingDetail?id={{$id}}" target="_blank">快速处理</a>。</p>
        <p>瑞招采服务平台</p>
    </body>
</html>
