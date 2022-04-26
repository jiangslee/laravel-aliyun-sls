<?php
return [
    'endpoint' => env('ALIYUN_LOG_ENDPOINT'),
    'accessKeyId' => env('ALIYUN_LOG_ACCESSKEYID'),
    'accessKey' => env('ALIYUN_LOG_ACCESSKEY'),
    'project' => env('ALIYUN_LOG_PROJECT'),
    'logstore' => env('ALIYUN_LOG_LOGSTORE'),
    'bufferLimit' => env('ALIYUN_LOG_BUFFER_LIMIT', 5),
    'formatter' => \Jiangslee\LaravelAliyunSls\Formatters\AliyunSlsFormatter::class
];