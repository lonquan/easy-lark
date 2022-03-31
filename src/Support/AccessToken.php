<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Support;

use AntCool\EasyLark\Interfaces\AccessTokenInterface;
use AntCool\EasyLark\Traits\InteractWithHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use AntCool\EasyLark\Kernel\Config;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Psr\SimpleCache\CacheInterface;

class AccessToken implements AccessTokenInterface
{
    use InteractWithHttpClient;

    protected CacheInterface $cache;

    public function __construct(protected Config $config, protected ?Logger $logger)
    {
        $this->cache = new Psr16Cache(
            new FilesystemAdapter(
                namespace: 'easy_lark',
                defaultLifetime: 7200,
                directory: $this->config->get('runtime_path', '/tmp/easy-lark') . '/cache/'
            )
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getToken(): string
    {
        if ($token = $this->cache->get($this->getKey(), false)) {
            return $token;
        }

        $response = $this->createHttp()->postJson('/open-apis/auth/v3/app_access_token/internal', [
            'app_id' => $this->config->get('app_id'),
            'app_secret' => $this->config->get('app_secret'),
        ]);

        $this->cache->set($this->getKey(), $response['app_access_token'], $response['expire']);

        return $response['app_access_token'];
    }

    protected function getKey(): string
    {
        return sprintf('token_%s', $this->config->get('app_id'));
    }
}
