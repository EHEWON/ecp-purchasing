<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】【{{$name}}】供应商投标通知</title>
  </head>
  <body>
    <p>您好，【{{$name}}】的招标项目，【{{$supplierName}}】已投标，请尽快登录系统查看投标信息。<a href="{{env('BOSS_URL')}}/front/#/inviteTenders/ProjectApprovalDetails?id={{$projectId}}" target="_blank">快速处理</a>。</p>
    <p>瑞招采服务平台</p>
  </body>
</html>
