<?php

declare(strict_types=1);
namespace Lonquan\EasyLark\Kernel\Contracts;

interface AccessToken
{
    public function getToken(): string;
}
