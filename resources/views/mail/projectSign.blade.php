<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】【{{$name}}】招标报名结果通知</title>
  </head>
  <body>
    <p>您好，【{{$name}}】已有供应商报名成功，请尽快登录系统查看报名信息。<a href="{{env('BOSS_URL')}}/front/#/inviteTenders/ProjectApprovalDetails?id={{$projectId}}" target="_blank">快速处理</a>。</p>
    <p>瑞招采服务平台</p>
  </body>
</html>