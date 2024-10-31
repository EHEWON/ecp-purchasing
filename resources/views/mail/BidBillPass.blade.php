<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】【{{$user['title']}}】竞价结果通知</title>
  </head>
  <body>
    <p>恭喜您，【{{$user['title']}}】已中标，请尽快登录系统查看。<a href="{{env('BOSS_URL')}}/front/#/biddingManage/biddingDetail?id={{$user['id']}}" target="_blank">快速处理</a></p>
  </body>
</html>
