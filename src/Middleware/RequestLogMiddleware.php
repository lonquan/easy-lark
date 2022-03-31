<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Middleware;

use AntCool\EasyLark\Kernel\Config;
use AntCool\EasyLark\Support\Logger;
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
        if ($this->logger instanceof Logger) {
            $body = $response->getBody();
            $body->rewind();
            $this->logger->info('Response ===<<<', [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => $body->getContents(),
            ]);
        }
    }

    protected function logRequest(RequestInterface $request): void
    {
        if ($this->logger instanceof Logger) {
            $body = $request->getBody();
            $body->rewind();
            $this->logger->info('Request ===>>>' . PHP_EOL, [
                'host' => $request->getUri()->getHost(),
                'url' => $request->getRequestTarget(),
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'body' => $body->getContents(),
            ]);
        }
    }
}
