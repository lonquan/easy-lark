<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Kernel;

use AntCool\EasyLark\Middleware\AccessTokenMiddleware;
use AntCool\EasyLark\Support\Logger;
use AntCool\EasyLark\Traits\InteractWithHttpClient;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

class Client
{
    use InteractWithHttpClient;

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
