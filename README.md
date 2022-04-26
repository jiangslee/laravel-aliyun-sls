# laravel-aliyun-sls

forked from islenbo/laravel-aliyun-sls

thanks islenbo!
## Using
```shell
composer require Jiangslee/laravel-aliyun-sls
```

add service provider to `config/app.php`
```PHP
<?php

return [
    // ...
    'providers' => [
        /*
         * Package Service Providers...
         */
        \Jiangslee\LaravelAliyunSls\Providers\LaravelAliyunSlsProvider::class,
    ],
    // ...
];
```

add config to logging.php
```PHP
<?php
return [
    // ...

    // Aliyun SLS config
    'aliyun-sls' => [
        'endpoint' => env('ALIYUN_LOG_ENDPOINT'),
        'accessKeyId' => env('ALIYUN_LOG_ACCESSKEYID'),
        'accessKey' => env('ALIYUN_LOG_ACCESSKEY'),
        'project' => env('ALIYUN_LOG_PROJECT'),
        'logstore' => env('ALIYUN_LOG_LOGSTORE'),
        'bufferLimit' => env('ALIYUN_LOG_BUFFER_LIMIT', 5),
        'formatter' => \Jiangslee\LaravelAliyunSls\Formatters\AliyunSlsFormatter::class
    ],

    // ...
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            // assign aliyun-sls
            'channels' => ['aliyun-sls'],
            'ignore_exceptions' => false,
            'tap' => [
            ]
        ],
        // ...
    ],
];
```
