<?php

namespace Lonquan\EasyLark\Kernel\Support;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Lonquan\EasyLark\Kernel\Config;
use Lonquan\EasyLark\Kernel\Contracts\AccessToken as AccessTokenInterface;
use Lonquan\EasyLark\Kernel\Traits\HttpClient;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Psr\SimpleCache\CacheInterface;

class AccessToken implements AccessTokenInterface
{
    use HttpClient;

    protected CacheInterface $cache;

    public function __construct(protected Config $config, protected ?Logger $logger)
    {
        $this->cache = new Psr16Cache(
            new FilesystemAdapter(
                namespace:       'easy_lark',
                defaultLifetime: 7200,
                directory:       $this->config->get('storage_path').'/cache'
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public function getToken(): string
    {
        $token = $this->cache->get($this->getKey());

        if ($token) {
            return $token;
        }

        $response = $this->createHttp()->postJson('/open-apis/auth/v3/app_access_token/internal', [
            'app_id'     => $this->config->get('app_id'),
            'app_secret' => $this->config->get('app_secret'),
        ]);

        $this->cache->set($this->getKey(), $response['app_access_token'], $response['expire']);

        return $response['app_access_token'];
    }

    public function withHandleStacks(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $this->withRequestLogMiddleware($stack);

        return $stack;
    }

    protected function getKey(): string
    {
        return sprintf('token_%s', $this->config->get('app_id'));
    }
}
