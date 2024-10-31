<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】您的企业认证信息变更已审核通过</title>
  </head>
  <body>
    <p>尊敬的 <strong>{{$user['email']}}</strong>，您好，</p>
    <p>恭喜您，您的企业认证信息变更已审核通过，请尽快登录系统获取更多的商机。<a href="{{env('BOSS_URL')}}/front/#/supplierClient/companyInfo" target="_blank">快速处理</a></p>
    <p></p>
    <p>瑞招采服务平台</p>
  </body>
</html>
