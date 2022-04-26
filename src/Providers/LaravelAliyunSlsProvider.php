<?php

namespace Jiangslee\LaravelAliyunSls\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Jiangslee\LaravelAliyunSls\Formatters\AliyunSlsFormatter;
use Jiangslee\LaravelAliyunSls\Handlers\AliyunSlsBufferHandler;
use Jiangslee\LaravelAliyunSls\Handlers\AliyunSlsHandler;

class LaravelAliyunSlsProvider extends ServiceProvider
{
    /**
     * Abstract type to bind Timber in the Service Container.
     *
     * @var string
     */
    public static $abstract = 'aliyunsls';

    /** @var Repository */
    private $config;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->config = $this->app->get('config');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/aliyunsls.php' => config_path(static::$abstract . '.php'),
        ], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->config->set('logging.channels.aliyun-sls', $this->getChannel());
    }

    private function getClientConfig()
    {
        return (new Collection($this->getChannel()))->get('handler_with.handlerConfig.handler_with');
    }

    private function getChannel(): array
    {
        $slsConfig = $this->config->get('aliyunsls');
        return [
            'driver' => 'monolog',
            'handler' => AliyunSlsBufferHandler::class,
            'handler_with' => [
                'handlerConfig' => [
                    'handler' => AliyunSlsHandler::class,
                    'handler_with' => [
                        'endpoint' => $slsConfig['endpoint'],
                        'accessKeyId' => $slsConfig['accessKeyId'],
                        'accessKey' => $slsConfig['accessKey'],
                        'project' => $slsConfig['project'],
                        'logstore' => $slsConfig['logstore'],
                    ],
                ],
                'bufferLimit' => $slsConfig['bufferLimit'],
            ],
            'formatter' => class_exists($slsConfig['formatter']) ? $slsConfig['formatter'] : AliyunSlsFormatter::class,
        ];
    }
}
