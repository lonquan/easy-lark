<?php

declare(strict_types=1);

namespace AntCool\EasyLark\Support;

use AntCool\EasyLark\Exceptions\InvalidArgumentException;
use GuzzleHttp\Psr7\Utils;

class File
{
    public string $name;

    public string $dir;

    public string $base;

    public string $extension;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(public string $file)
    {
        if (file_exists($this->file) == false) {
            throw new InvalidArgumentException('File does not exist.');
        }

        $pathInfo = pathinfo($this->file);
        $this->name = $pathInfo['filename'];
        $this->dir = $pathInfo['dirname'];
        $this->base = $pathInfo['basename'];
        $this->extension = $pathInfo['extension'];
    }

    public function getContents()
    {
        return Utils::tryFopen($this->file, 'r');
    }
}
