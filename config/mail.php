<?php

return [
    'default' => 'default',
    'manage_mail' => env('MANAGE_MAIL'),
    'mailers' => [
        'pushservice' => [
            'transport' => env('MAIL_DRIVER', 'smtp'),
            'driver' => env('MAIL_DRIVER', 'smtp'),
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION', null),
            'from' => ['address' => env('MAIL_ACCOUNT'), 'name' => env('MAIL_ACCOUNT')],
            'username' => env('MAIL_ACCOUNT'),
            'password' => env('MAIL_PWD'),
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        ],
        'default' => [
            'transport' => env('MAIL_DRIVER', 'smtp'),
            'driver' => env('MAIL_DRIVER', 'smtp'),
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION', null),
            'from' => ['address' => env('MAIL_ACCOUNT'), 'name' => env('MAIL_ACCOUNT')],
            'username' => env('MAIL_ACCOUNT'),
            'password' => env('MAIL_PWD'),
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        ]
    ]
];
