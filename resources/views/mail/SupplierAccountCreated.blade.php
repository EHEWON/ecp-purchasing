<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>瑞招采平台账户已创建成功</title>
    </head>
    <body>
        <p>尊敬的 <strong>{{$user['phone_email']}}</strong>，您好，</p>
        <p>恭喜您，已成功注册瑞招采平台会员，登录账号：【{{$user['phone_email']}}】,初始密码：111111，请尽快登录系统<a href="{{env('BOSS_URL')}}" target="_blank">{{env('BOSS_URL')}}</a>修改密码。
        </p>
    </body>
</html>
