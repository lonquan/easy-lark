<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Interfaces;

interface AccessTokenInterface
{
    public function getToken(): string;
}
