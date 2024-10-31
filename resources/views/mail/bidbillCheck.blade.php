<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>【{{env('APP_NAME')}}】【{{$name}}】竞价资审通知</title>
  </head>
  <body>
    <p>您好，【{{$name}}】已报名截止，请及时完成资审。<a href="{{env('BOSS_URL')}}/front/#/bidding/BiddingDetails?id={{$id}}" target="_blank">快速处理</a>。</p>
    <p>瑞招采服务平台</p>
  </body>
</html>
