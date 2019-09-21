<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\ValueObject;

use InvalidArgumentException;
use function sprintf;

final class Rank
{
    const MIN_RANK = 1;
    const MAX_RANK = 13;

    private $value;

    public function __construct(int $value)
    {
        if ($value < static::MIN_RANK || $value > static::MAX_RANK) {
            throw new InvalidArgumentException(
                sprintf('Value for %s must be between %d and %d', static::class, static::MIN_RANK, static::MAX_RANK)
            );
        }
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
