<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】您提交企业“{{$user['supplier_name']}}”认证信息冻结申请已被拒绝。</title>
  </head>
  <body>
    <p>尊敬的 <strong>{{$user['email']}}</strong>，您好，</p>
    <p>您提交企业“{{$user['supplier_name']}}”认证信息冻结申请已被拒绝,请尽快登录系统查看原因。<a href="{{env('BOSS_URL')}}/front/#/supplierManage/supplierDetail?id={{$user['supplier_id']}}&dataType=info" target="_blank">快速处理</a></p>
    <p></p>
    <p>瑞招采服务平台</p>
  </body>
</html>
