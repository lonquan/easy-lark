<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Kernel;

use AntCool\EasyLark\Exceptions\InvalidArgumentException;
use AntCool\EasyLark\Interfaces\EventInterface;
use AntCool\EasyLark\Support\Utils;
use AntCool\EasyLark\Traits\HasAttributes;

class Event implements EventInterface, \ArrayAccess
{
    use HasAttributes;

    protected bool $encrypted;

    public function __construct(array $attributes, ?string $encryptKey = null)
    {
        if (array_key_exists('encrypt', $attributes)) {
            if (empty($encryptKey)) {
                throw new InvalidArgumentException('Encrypt key is required when event is encrypted.');
            }

            $attributes = json_decode(Utils::aesDecrypt($attributes['encrypt'], $encryptKey), true);

            $this->encrypted = true;
        }

        $this->attributes = $attributes;
    }

    public function encrypted(): bool
    {
        return $this->encrypted;
    }

    public function isEventType(string $type): bool
    {
        return $this->getEventType() === $type;
    }

    public function getEventType(): ?string
    {
        return match (true) {
            $this->get('type') === 'url_verification' => 'url_verification',
            $this->getEvevntVersion() === '1.0' => $this->get('event')['type'] ?? null,
            $this->getEvevntVersion() === '2.0' => $this->get('header')['event_type'] ?? null,
        };
    }

    public function getHeader(): array
    {
        return match ($this->getEvevntVersion()) {
            '1.0' => [
                'event_id' => $this->getEventId(),
                'event_type' => $this->getEventType(),
                'create_time' => $this->getCreateTime(),
                'token' => $this->getToken(),
                'app_id' => $this->get('event')['app_id'] ?? null,
                'tenant_key' => $this->get('event')['tenant_key'] ?? null,
            ],
            '2.0' => $this->get('header', []),
        };
    }

    public function getBody(): array
    {
        return $this->get('event', []);
    }

    public function getEvevntVersion(): string
    {
        return $this->get('schema') === '2.0' ? '2.0' : '1.0';
    }

    public function getEventId(): ?string
    {
        return match ($this->getEvevntVersion()) {
            '1.0' => $this->get('uuid'),
            '2.0' => $this->get('header')['event_id'] ?? null,
        };
    }

    public function getCreateTime(): ?string
    {
        return match (true) {
            $this->getEvevntVersion() === '1.0' => $this->get('ts'),
            $this->getEvevntVersion() === '2.0' => $this->get('header')['create_time'] ?? null,
        };
    }

    public function getToken(): ?string
    {
        return match ($this->getEvevntVersion()) {
            '1.0' => $this->get('token'),
            '2.0' => $this->get('header')['token'] ?? null,
            default => $this->get('token'), // Url verify event
        };
    }
}
