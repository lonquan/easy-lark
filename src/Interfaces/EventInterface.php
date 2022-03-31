<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Interfaces;

interface EventInterface
{
    public function getEvevntVersion(): string;

    public function getEventId(): ?string;

    public function getEventType(): ?string;

    public function isEventType(string $type): bool;

    public function getCreateTime(): ?string;

    public function getHeader(): ?array;

    public function getBody(): ?array;

    public function getToken(): ?string;
}
