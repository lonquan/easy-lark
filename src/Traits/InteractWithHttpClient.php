<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Traits;

use AntCool\EasyLark\Middleware\RequestLogMiddleware;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use AntCool\EasyLark\Exceptions\ResponseInvalidException;

trait InteractWithHttpClient
{
    protected Http $http;

    public function getJson(string $uri, array $query = []): array
    {
        return $this->request(method: 'GET', uri: $uri, options: ['query' => $query]);
    }

    /**
     * @throws GuzzleException
     * @throws ResponseInvalidException
     */
    public function postJson(string $uri, array $data = [], array $query = []): array
    {
        return $this->request(method: 'POST', uri: $uri, options: [
            'query' => $query,
            'json' => $data,
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws ResponseInvalidException
     */
    public function request(string $method, string $uri, $options = []): array
    {
        $response = $this->http->request($method, $uri, $options);
        $body = $response->getBody();
        $body->rewind();
        $response = json_decode($body->getContents(), true);

        if (($response['code'] ?? '') !== 0) {
            throw new ResponseInvalidException($response['msg'] ?? 'Incorrect response.', $response['code'] ?? 0);
        }

        return $response;
    }

    protected function createHttp(): self
    {
        if (empty($this->http)) {
            $this->http = new Http([
                'base_uri' => $this->config->http['base_uri'] ?? 'https://open.feishu.cn',
                'timeout' => $this->config->http['timeout'] ?? 30,
                'handler' => $this->withHandleStacks(),
            ]);
        }

        return $this;
    }

    protected function withHandleStacks(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $this->withRequestLogMiddleware($stack);

        return $stack;
    }

    protected function withRequestLogMiddleware(HandlerStack $stock): void
    {
        if ($this->config->get('debug', false)) {
            $stock->push(new RequestLogMiddleware($this->config, $this->logger));
        }
    }
}
