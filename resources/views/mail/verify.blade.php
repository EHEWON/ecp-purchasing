<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>用户注册</title>
  </head>
  <body>
    <p>尊敬的 <strong>{{$user['phone_email']}}</strong>，您好，</p>
    <p>您正在请求注册平台的验证码，验证码有效期{{$user['expired']}}，请尽快使用。 如非您本人操作，请忽略此邮件。</p>
    <strong>{{$user['code']}}</strong>
    <p></p>
    <p>瑞招采服务平台</p>
  </body>
</html>
