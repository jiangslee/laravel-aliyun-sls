<?php

namespace Jiangslee\LaravelAliyunSls\Kernel\Traits;

use Jiangslee\LaravelAliyunSls\Kernel\Contracts\ConfigInterface;

trait WithConfig
{
    protected array|ConfigInterface $config;

    /**
     * @param array|ConfigInterface<string,mixed> $config
     */
    public function __construct(array|ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getConfig(): array|ConfigInterface
    {
        return $this->config;
    }

    public function setConfig(array|ConfigInterface $config): static
    {
        $this->config = $config;

        return $this;
    }
}
