<?php
namespace Jiangslee\LaravelAliyunSls\Kernel\Traits;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
trait WithHttpClient {

    protected ?HttpClientInterface $httpClient = null;
    public function createClient(): HttpClientInterface
    {
        $httpClient = $this->getHttpClient();
        return $httpClient;
    }

    public function getHttpClient(): HttpClientInterface
    {
        if (!$this->httpClient) {
            $this->httpClient = $this->createHttpClient();
        }

        return $this->httpClient;
    }

    public function setHttpClient(HttpClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    protected function createHttpClient(): HttpClientInterface
    {
        return HttpClient::create($this->getHttpClientDefaultOptions());
    }

    /**
     * @return array<string,mixed>
     */
    protected function getHttpClientDefaultOptions(): array
    {
        return [];
    }
}