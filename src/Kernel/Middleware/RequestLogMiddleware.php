<?php

declare(strict_types=1);
namespace Lonquan\EasyLark\Kernel\Middleware;

use Lonquan\EasyLark\Kernel\Config;
use Lonquan\EasyLark\Kernel\Support\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestLogMiddleware
{
    public function __construct(protected Config $config, protected ?Logger $logger)
    {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $promise = $handler($request, $options);

            $this->logRequest($request);

            return $promise->then(
                function (ResponseInterface $response) {
                    $this->logResponse($response);

                    return $response;
                }
            );
        };
    }

    protected function logResponse(ResponseInterface $response): void
    {
        $body = $response->getBody();
        $body->rewind();
        $this->logger?->info('Response', [
            'status'  => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body'    => $body->getContents(),
        ]);
    }

    protected function logRequest(RequestInterface $request): void
    {
        $body = $request->getBody();
        $body->rewind();
        $this->logger?->info('Request', [
            'url'     => $request->getRequestTarget(),
            'method'  => $request->getMethod(),
            'headers' => $request->getHeaders(),
            'body'    => $body->getContents(),
        ]);
    }
}
