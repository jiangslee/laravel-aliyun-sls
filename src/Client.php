<?php

namespace Jiangslee\LaravelAliyunSls;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log as FacadesLog;
use Jiangslee\LaravelAliyunSls\AliyunLogModel\Log;
use Jiangslee\LaravelAliyunSls\AliyunLogModel\LogContent;
use Jiangslee\LaravelAliyunSls\AliyunLogModel\LogGroup;
use Jiangslee\LaravelAliyunSls\AliyunLogModel\LogItem;
use Jiangslee\LaravelAliyunSls\Kernel\Contracts\ClientInterface;
use Jiangslee\LaravelAliyunSls\Kernel\Traits\WithConfig;
use Jiangslee\LaravelAliyunSls\Kernel\Traits\WithHttpClient;
use Jiangslee\LaravelAliyunSls\Kernel\Utils;

class Client implements ClientInterface
{
    use WithHttpClient;
    use WithConfig;

    /**
     * API版本
     */
    public const API_VERSION = '0.6.0';

    public function putLogs(array $logItems, string $source_ip = '', string $receive_time = '', string $topic = 'default'): void
    {
        $source_ip = $source_ip ?? Utils::getLocalIp();
        $receive_time = $receive_time ?? now();
        $project = $this->config->get('project', 'default');
        $logstore = $this->config->get('logstore', 'demo-log');
        $topic = $topic ?? 'default-topic';
        $resource = "/logstores/$logstore/shards/lb";

        $params = [];
        $headers = [];

        $logGroup = new LogGroup();
        $logGroup->setTopic($topic);
        $logGroup->setSource($source_ip);

        foreach ($logItems as $logItem) {
            if ($logItem instanceof LogItem) {
                $log = new Log();
                $log->setTime($logItem->getTime());
                $contents = $logItem->getContents();
                foreach ($contents as $key => $value) {
                    $content = new LogContent();
                    $content->setKey($key);
                    $content->setValue($value);
                    $log->addContents($content);
                };
                $logGroup->addLogs($log);
            }
        }


        $body = Utils::toBytes($logGroup);
        unset($logGroup);
        unset($logItem);
        unset($log);
        unset($contents);

        $bodySize = strlen($body);

        if ($bodySize > 3 * 1024 * 1024) {
            throw new \RuntimeException('PutLogs 接口每次可以写入的日志组数据量上限为3MB!');
        }
        $headers["x-log-bodyrawsize"] = $bodySize;
        $headers['x-log-compresstype'] = 'deflate';
        $headers['Content-Type'] = 'application/x-protobuf';

        $body = gzcompress($body, 6);

        $data = [
            'method' => "POST",
            'project' => $project,
            'body' => $body,
            'resource' => $resource,
            'params' => $params,
            'headers' => $headers
        ];

        try {
            $res = $this->send($data);
        } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {

            // dd($e);
            FacadesLog::channel('dialy')->error($e->getMessage());
        } catch (\Throwable $th) {
            // dd($th);
            FacadesLog::channel('dialy')->error($th->getMessage());
        }
    }

    public function send(array $data)
    {
        $accessKey = $this->config->get('accessKeyId', '');
        $secretKey = $this->config->get('accessKey', '');
        $logstore = $this->config->get('logstore', 'demo-log');
        $endpoint = $this->config->get('endpoint', 'cn-shenzhen.log.aliyuncs.com');

        $method = Arr::get($data, 'method', 'POST');
        $project = Arr::get($data, 'project', '');
        $resource = Arr::get($data, 'resource', "/logstores/$logstore/shards/lb");
        $params = Arr::get($data, 'params', '');
        $headers = Arr::get($data, 'headers', []);
        $body = Arr::get($data, 'body', '');

        $host = is_null($project) ? $endpoint : "{$project}.{$endpoint}";

        if ($body) {
            $headers['Content-Length'] = strlen($body);
            $headers['Content-MD5'] = \strtoupper(\md5($body));
            $headers["x-log-bodyrawsize"] = $headers["x-log-bodyrawsize"] ?? 0;
        } else {
            $headers['Content-Length'] = 0;
            $headers["x-log-bodyrawsize"] = 0;
            $headers['Content-Type'] = 'application/text';
        }

        $headers['x-log-apiversion'] = self::API_VERSION;
        $headers['x-log-signaturemethod'] = 'hmac-sha1';
        $headers['Host'] = $host;
        $headers['Date'] = gmdate('D, d M Y H:i:s') . ' GMT';

        $signature = Utils::getSignature($method, $resource, $secretKey, $params, $headers);
        $headers['Authorization'] = "LOG $accessKey:$signature";

        $url = "https://{$host}{$resource}";

        if ($params) {
            $url .= '?' . \http_build_query($params);
        }

        return $this->createClient()->request($method, $url, [
            'headers' => $headers,
            'body' => $body
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    protected function getHttpClientDefaultOptions(): array
    {
        return \array_merge(
            $this->config->get('http', [])
        );
    }
}
