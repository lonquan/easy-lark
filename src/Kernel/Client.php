<?php

declare(strict_types=1);
namespace AntCool\EasyLark\Kernel;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use AntCool\EasyLark\Kernel\Middleware\AccessTokenMiddleware;
use AntCool\EasyLark\Kernel\Support\Logger;
use AntCool\EasyLark\Kernel\Traits\HttpClient;

class Client
{
    use HttpClient;

    public function __construct(protected Config $config, protected ?Logger $logger)
    {
        $this->createHttp();
    }

    public function withHandleStacks(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(new AccessTokenMiddleware($this->config, $this->logger));
        $this->withRequestLogMiddleware($stack);

        return $stack;
    }
}
