<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use ArrayIterator;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;

final class HandTypeIterator extends ArrayIterator
{
    public function current(): HandType
    {
        return parent::current();
    }

    /**
     * @return HandType[]
     */
    public function getArrayCopy()
    {
        return parent::getArrayCopy();
    }
}
