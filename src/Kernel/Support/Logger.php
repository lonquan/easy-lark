<?php

declare(strict_types=1);
namespace AntCool\EasyLark\Kernel\Support;

use AntCool\EasyLark\Kernel\Config;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    protected MonologLogger $logger;

    public function __construct(protected Config $config)
    {
        $this->logger = new MonologLogger('EasyLark');
        $this->logger->pushHandler(
            new RotatingFileHandler($this->config->get('storage_path').'/logs/easy_lark.log', 30)
        );
    }

    public function __call(string $name, array $arguments)
    {
        call_user_func_array([$this->logger, $name], $arguments);
    }
}
