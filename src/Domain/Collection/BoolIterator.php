<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use ArrayIterator;

final class BoolIterator extends ArrayIterator
{
    public function current(): Bool
    {
        return parent::current();
    }

    /**
     * @return bool[]
     */
    public function getArrayCopy()
    {
        return parent::getArrayCopy();
    }
}
