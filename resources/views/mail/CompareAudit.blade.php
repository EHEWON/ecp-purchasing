<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$user['purchaserName']}}</title>
  </head>
  <body>
    <p>{{$user['purchaserName']}}，请尽快登录系统<a href="{{env('BOSS_URL')}}/front/#/inquiryRate/competitiveDetails?id={{$user['compareId']}}" target="_blank">{{env('BOSS_URL')}}</a>查看。</p>
    <p></p>
    <p>瑞招采服务平台</p>
  </body>
</html>
