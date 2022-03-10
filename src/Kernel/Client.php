<?php

declare(strict_types=1);
namespace Lonquan\EasyLark\Kernel;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Lonquan\EasyLark\Kernel\Middleware\AccessTokenMiddleware;
use Lonquan\EasyLark\Kernel\Support\Logger;
use Lonquan\EasyLark\Kernel\Traits\HttpClient;

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
