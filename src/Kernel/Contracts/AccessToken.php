<?php

declare(strict_types=1);
namespace AntCool\EasyLark\Kernel\Contracts;

interface AccessToken
{
    public function getToken(): string;
}
