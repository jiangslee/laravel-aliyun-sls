<?php

namespace Jiangslee\LaravelAliyunSls\Kernel\Contracts;

interface ClientInterface
{
    // Put logs to Log Service.
    public function putLogs(array $contents): void;
}
