<?php

declare(strict_types=1);
namespace Lonquan\EasyLark\Kernel\Middleware;

use Lonquan\EasyLark\Kernel\Config;
use Lonquan\EasyLark\Kernel\Contracts\AccessToken;
use Lonquan\EasyLark\Kernel\Support\Cache;
use Lonquan\EasyLark\Kernel\Support\Logger;
use Psr\Http\Message\RequestInterface;

class AccessTokenMiddleware
{
    protected AccessToken $accessToken;

    public function __construct(protected Config $config, protected ?Logger $logger)
    {
        $this->accessToken = new ($this->config->get('access_token'))($this->config, $this->logger);
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader(
                'Authorization',
                'Bearer '.$this->accessToken->getToken()
            );

            return $handler($request, $options);
        };
    }
}
