<?php

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Jiangslee\LaravelAliyunSls\Client;
use Jiangslee\LaravelAliyunSls\Formatters\AliyunSlsFormatter;
use Jiangslee\LaravelAliyunSls\Kernel\Config;

require __DIR__.'/../vendor/autoload.php';

if (is_file('./' . '.env')) {
    $env = parse_ini_file('./' . '.env', true);
    foreach ($env as $key => $val) {
        $name = strtoupper($key);
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $item = $name . '_' . strtoupper($k);
                putenv("$item=$v");
            }
        } else {
            putenv("$name=$val");
        }
    }
}

$config = new Config([
    'accessKeyId' => env('ALIYUN_LOG_ACCESSKEYID', '----YOUR-ALIYUN-ACCESS-KEY----'),
    'accessKey' => env('ALIYUN_LOG_ACCESSKEY', '----YOUR-ALIYUN-SECRET-KEY----'),
    'endpoint' => env('ALIYUN_LOG_ENDPOINT', 'cn-shenzhen.log.aliyuncs.com'),
    'project' => env('ALIYUN_LOG_PROJECT', '----YOUR-ALIYUN-LOG-PROJECT----'),
    'logstore' => env('ALIYUN_LOG_LOGSTORE', '----YOUR-ALIYUN-LOG-STORE----'),
]);

$client = new Client($config);

$log = [
    'datetime' => new DateTime(),
    'message' => 'hello',
    'level_name' => 'info',
    'channel' => 'default',
    'context' => [
        'data' => '123456',
        'msg' => 'test',
        'contents' => Str::random(),
        'uuid' => str::orderedUuid(),
    ],
    'extra' => [],
];

$logItems = (new AliyunSlsFormatter())->format($log);

$client->putLogs([$logItems]);
