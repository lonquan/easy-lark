<?php

declare(strict_types=1);
namespace AntCool\EasyLark;

use AntCool\EasyLark\Kernel\Client;
use AntCool\EasyLark\Kernel\Config;
use AntCool\EasyLark\Kernel\Server;
use AntCool\EasyLark\Kernel\Support\Logger;

class Application
{
    protected Config $config;

    protected Client $client;

    protected Server $server;

    protected ?Logger $logger = null;

    /**
     * @throws Kernel\Exceptions\InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);

        if ($this->config->get('debug', false)) {
            $this->logger = new Logger($this->config);
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getClient(): Client
    {
        return $this->client ?? $this->client = new Client($this->config, $this->logger);
    }

    public function getServer(): Server
    {
        return $this->server ?? $this->server = new Server($this->config, $this->logger);
    }
}
