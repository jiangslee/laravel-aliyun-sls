# laravel-aliyun-sls

forked from islenbo/laravel-aliyun-sls
thanks islenbo!

# Notes
- 由于`aliyun-log-sdk`的`\Log`类与`laravel-ide-helper`的`\Log`类有冲突，所以抄了过来放到了`AliyunLogModel`
- 抄了`@overtrue` `easywechat6.x`的`HttpClient Traits`
- 写了个`Demo.php`跑，配置好`.env`后，执行`php ./src/Demo.php`可以先看看日志能不能跑进`sls`
- 由于`BufferHandler`不适合在队例使用，去掉了`BufferHandler`

# Todo list
1. 用队列写日志？
2. `HttpClient`支持Laravel-S协程？

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

add config to aliyunsls.php
```PHP

    // Aliyun SLS config
    return [
        'endpoint' => env('ALIYUN_LOG_ENDPOINT', 'cn-shenzhen.log.aliyuncs.com'),
        'accessKeyId' => env('ALIYUN_LOG_ACCESSKEYID'),
        'accessKey' => env('ALIYUN_LOG_ACCESSKEY'),
        'project' => env('ALIYUN_LOG_PROJECT'),
        'logstore' => env('ALIYUN_LOG_LOGSTORE'),
        'formatter' => \Jiangslee\LaravelAliyunSls\Formatters\AliyunSlsFormatter::class
    ],
```

add or edit default channel to .env
```INI
LOG_CHANNEL=aliyun-sls
```

add config to logging.php
```PHP
<?php
return [
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

# Other use case

```PHP
Log::channel('aliyun-sls')->info('hello world!');
```