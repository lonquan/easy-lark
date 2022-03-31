<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Middleware;

use AntCool\EasyLark\Interfaces\AccessTokenInterface;
use AntCool\EasyLark\Kernel\Config;
use AntCool\EasyLark\Support\AccessToken;
use AntCool\EasyLark\Support\Logger;
use Psr\Http\Message\RequestInterface;

class AccessTokenMiddleware
{
    protected AccessTokenInterface $accessToken;

    public function __construct(protected Config $config, protected ?Logger $logger)
    {
        $class = $this->config->access_token && class_exists($this->config->access_token)
            ? $this->config->access_token
            : AccessToken::class;

        $this->accessToken = new ($class)($this->config, $this->logger);
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader(
                'Authorization',
                'Bearer ' . $this->accessToken->getToken()
            );

            return $handler($request, $options);
        };
    }
}
