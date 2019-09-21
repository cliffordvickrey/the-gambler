<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use ArrayIterator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;

final class DrawIterator extends ArrayIterator
{
    public function current(): Draw
    {
        return parent::current();
    }

    /**
     * @return Draw[]
     */
    public function getArrayCopy()
    {
        return parent::getArrayCopy();
    }
}
