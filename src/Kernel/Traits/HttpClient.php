<?php

declare(strict_types=1);
namespace AntCool\EasyLark\Kernel\Traits;

use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use AntCool\EasyLark\Kernel\Exceptions\ResponseInvalidException;
use AntCool\EasyLark\Kernel\Middleware\RequestLogMiddleware;

trait HttpClient
{
    protected Http $http;

    /**
     * @throws GuzzleException
     * @throws ResponseInvalidException
     */
    public function postJson(string $uri, $data = [], $query = [])
    {
        return $this->request(method: 'POST', uri: $uri, options: [
            'query' => $query,
            'json'  => $data,
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
            throw new ResponseInvalidException($response['msg'] ?? '', $response['code'] ?? 0);
        }

        return $response;
    }

    protected function createHttp(): self
    {
        if (empty($this->http)) {
            $this->http = new Http(
                [
                    'base_uri' => $this->config->get('base_url', 'https://open.feishu.cn'),
                    'timeout'  => $this->config->get('timeout', 30),
                    'handler'  => $this->withHandleStacks(),
                ]
            );
        }

        return $this;
    }

    protected function withRequestLogMiddleware(HandlerStack $stock): void
    {
        if ($this->config->get('debug', false)) {
            $stock->push(new RequestLogMiddleware($this->config, $this->logger));
        }
    }
}
