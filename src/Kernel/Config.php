<?php
declare(strict_types=1);

namespace AntCool\EasyLark\Kernel;

use AntCool\EasyLark\Exceptions\InvalidArgumentException;
use AntCool\EasyLark\Traits\HasAttributes;

class Config implements \ArrayAccess
{
    use HasAttributes;

    protected array $requiredKeys = [
        'app_id',
        'app_secret',
    ];

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
            throw new InvalidArgumentException(sprintf("\"%s\" cannot be empty.", join(',', $missingKeys)));
        }

        return true;
    }
}
