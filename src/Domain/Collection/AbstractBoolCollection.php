<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use Cliffordvickrey\TheGambler\Domain\Contract\CollectionInterface;
use function count;

class AbstractBoolCollection implements CollectionInterface
{
    /** @var bool[] */
    protected $values = [];

    public function getIterator(): BoolIterator
    {
        return new BoolIterator($this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }
}
