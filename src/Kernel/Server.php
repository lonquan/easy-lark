<?php

declare(strict_types=1);
namespace AntCool\EasyLark\Kernel;

use AntCool\EasyLark\Kernel\Support\Logger;

class Server
{
    public function __construct(protected Config $config, protected Logger $logger)
    {
    }
}
