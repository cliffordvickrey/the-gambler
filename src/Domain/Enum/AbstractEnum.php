<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Enum;

use InvalidArgumentException;

abstract class AbstractEnum
{
    protected static $enum = [];
    protected $value;

    public function __construct(string $value)
    {
        if (!isset(static::$enum[$value])) {
            throw new InvalidArgumentException(sprintf('Invalid valid for %s, "%s"', static::class, $value));
        }
        $this->value = $value;
    }

    public static function getEnum(): array
    {
        return static::$enum;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
