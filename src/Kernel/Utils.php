<?php

namespace Jiangslee\LaravelAliyunSls\Kernel;

use Illuminate\Support\Arr;

class Utils
{
    public static function getSignature($method, $resource, $accessKeySecret, $params, $headers)
    {
        if (!$accessKeySecret) {
            return '';
        }
        $content = $method . "\n";
        if (isset($headers['Content-MD5'])) {
            $content .= $headers['Content-MD5'];
        }
        $content .= "\n";
        if (isset($headers['Content-Type'])) {
            $content .= $headers['Content-Type'];
        }
        $content .= "\n";
        $content .= Arr::get($headers, 'Date') . "\n";
        $content .= self::handleLOGHeaders($headers) . "\n";
        $content .= self::handleResource($resource, $params);
        return self::hmacSHA1($content, $accessKeySecret);
    }
    public static function handleLOGHeaders($header)
    {
        ksort($header);
        $content = '';
        $first = true;
        foreach ($header as $key => $value) {
            if (strpos($key, "x-log-") === 0 || strpos($key, "x-acs-") === 0) {
                if ($first) {
                    $content .= $key . ':' . $value;
                    $first = false;
                } else {
                    $content .= "\n" . $key . ':' . $value;
                }
            }
        }
        return $content;
    }
    public static function hmacSHA1($content, $key)
    {
        $signature = hash_hmac("sha1", $content, $key, true);
        return base64_encode($signature);
    }
    public static function handleResource($resource, $params)
    {
        if ($params) {
            ksort($params);
            $urlString = "";
            $first = true;
            foreach ($params as $key => $value) {
                if ($first) {
                    $first = false;
                    $urlString = "$key=$value";
                } else {
                    $urlString .= "&$key=$value";
                }
            }
            return $resource . '?' . $urlString;
        }
        return $resource;
    }

    public static function toBytes($logGroup)
    {
        $mem = fopen("php://memory", "rwb");
        $logGroup->write($mem);
        rewind($mem);
        $bytes = "";

        if (feof($mem) === false) {
            $bytes = fread($mem, 10 * 1024 * 1024);
        }
        fclose($mem);
        return $bytes;
    }

    public static function getLocalIp()
    {
        $ip = 'unknow_ip';

        if (isset($_SERVER)) {
            $ip = Arr::get($_SERVER, 'SERVER_ADDR') ?? Arr::get($_SERVER, 'LOCAL_ADDR');
        } else {
            $ip = getenv('SERVER_ADDR');
        }

        if (function_exists('swoole_get_local_ip')) {
            $ips = array_values(\swoole_get_local_ip());
            foreach ($ips as $v) {
                if ($v && $v != $ip) {
                    $ip = $v;
                    break;
                }
            }
        }

        return $ip;
    }
}
