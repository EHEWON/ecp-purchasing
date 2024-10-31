<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】【{{$name}}】竞价报名结果通知</title>
  </head>
  <body>
    <p>您好，【{{$name}}】还有10分钟即将启动，如需调整竞价启动时间，请尽快前往系统处理，<a href="{{env('BOSS_URL')}}/front/#/bidding/BiddingDetails?id={{$id}}" target="_blank">快速处理</a>。</p>
    <p>瑞招采服务平台</p>
  </body>
</html>
