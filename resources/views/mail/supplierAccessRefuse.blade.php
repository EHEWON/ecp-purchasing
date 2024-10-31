<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】您提交的企业认证信息变更已被拒绝。</title>
  </head>
  <body>
    <p>尊敬的 <strong>{{$user['email']}}</strong>，您好，</p>
    <p>您向【{{$user['purchaser_name']}}】提交的准入申请已被拒绝，请尽快登录系统<a href="{{env('BOSS_URL')}}/front/#/supplierClient/apply?purchaser_id={{$user['purchaser_id']}}" target="_blank">{{env('BOSS_URL')}}</a>查看原因。</p>
    <p></p>
    <p>瑞招采服务平台</p>
  </body>
</html>
