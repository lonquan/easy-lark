<?php

declare(strict_types=1);
namespace Lonquan\EasyLark\Kernel;

use Lonquan\EasyLark\Kernel\Support\Logger;

class Server
{
    public function __construct(protected Config $config, protected Logger $logger)
    {
    }
}
