<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】【{{$name}}】已发布报价邀请</title>
  </head>
  <body>
    <p>您好，【{{$name}}】已向您发布报价邀请，请尽快登录系统报价。<a href="{{env('BOSS_URL')}}/front/#/quoteManage/inquiryDetail?id={{$inquiryId}}" target="_blank">快速处理</a></p>
    <p></p>
    <p>瑞招采服务平台</p>
  </body>
</html>
