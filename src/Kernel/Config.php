<?php

declare(strict_types=1);
namespace AntCool\EasyLark\Kernel;

use AntCool\EasyLark\Kernel\Exceptions\InvalidArgumentException;

class Config implements \ArrayAccess
{
    protected array $requiredKeys = [
        'app_id',
        'app_secret',
    ];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(protected array $items = [])
    {
        $this->checkMissingKeys();
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(array|string $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return $this->items[$key] ?? $default;
    }

    public function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = $this->items[$key] ?? $default;
        }

        return $config;
    }

    public function set(string $key, mixed $value = null): void
    {
        $this->items[$key] = $value;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function offsetExists(mixed $key): bool
    {
        return $this->has(\strval($key));
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->get(\strval($key));
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set(\strval($key), $value);
    }

    public function offsetUnset(mixed $key): void
    {
        $this->set(\strval($key), null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function checkMissingKeys(): bool
    {
        if (empty($this->requiredKeys)) {
            return true;
        }

        $missingKeys = [];

        foreach ($this->requiredKeys as $key) {
            if (!$this->has($key)) {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            throw new InvalidArgumentException(sprintf("\"%s\" cannot be empty.\r\n", \join(',', $missingKeys)));
        }

        return true;
    }
}
