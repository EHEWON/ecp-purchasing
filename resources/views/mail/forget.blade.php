<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>忘记密码</title>
  </head>
  <body>
    <p>尊敬的 <strong>{{$user['email']}}</strong>，您好，</p>
    <p>您正在请求更改密码的验证码，验证码有效期{{$user['expired']}}，请尽快使用。 如非您本人操作，请忽略此邮件。</p>
    <strong>{{$user['code']}}</strong>
    <p></p>
    <p>易和网，全球泛工业领域外贸综合服务平台</p>
    <p>
      <a href="{{env('MALL_URL')}}" target="_blank">{{env('MALL_URL_NO_HTTP')}}</a>
    </p>
    <p>
      <a href="{{env('MALL_URL')}}" target="_blank">
        <img style="width: 128px;" src="{{env('FILE_HOST')}}/group1/M00/00/00/rBFIw2I65zKAHbkFAADxj2CkeEU808.png" />
      </a>
    </p>
  </body>
</html>
