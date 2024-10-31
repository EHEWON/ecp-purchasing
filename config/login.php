<?php

return [
    'failed_count' => env('failed_count', 5), //登录失败次数
    'forbid_hours' => env('forbid_hours', 1), //达到最大登录失败次数后禁用小时数
    'token_expired' => env('token_expired', 86400*7), // 登录token有效期
    'email_expired' => env('email_expired', 60*60*4), // 邮件验证码有效期
    'org_expired' => env('org_expired', 86400*7), // 组织机构有效期
    'org_url'=>'https://v.apistore.cn/api/c105', // 阿里组织机构调用地址
    'org_appcode'=>'XzFu54qYpHIDpInjBYkUqDtVPQclWf4C', // 阿里组织机构appcode
];