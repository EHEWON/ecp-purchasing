<?php

return [
    'env' => env('APP_ENV'), //development 开发  production 生产
    'debug' => env('APP_DEBUG', false),
    'locale' => 'zh',
    'timezone' => 'PRC',
    'token_key' => 'EHKHHP54PXKYTS2E',
    'token_exp' => '2592000',
    'log' => 'daily',
    'APP_PATH' => './public',
    'site_url' => env('APP_URL'),
    'ali_pay_appid' => env('ALI_PAY_APPID'),
    'alipay_merchant_private_key' => env('ALIPAY_MERCHANT_PRIVATE_KEY'),
    'alipay_public_key' => env('ALIPAY_PUBLIC_KEY'),
    'alipay_notifyurl' => env('ALIPAY_NOTIFYURL'),
    'default_quote_count' => env('DEFAULT_QUOTE_COUNT', 10),
    'providers' => [
        // ...
        Spatie\Permission\PermissionServiceProvider::class,
    ]
];

