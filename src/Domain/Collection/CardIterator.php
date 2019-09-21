<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use ArrayIterator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;

final class CardIterator extends ArrayIterator
{
    public function current(): Card
    {
        return parent::current();
    }

    /**
     * @return Card[]
     */
    public function getArrayCopy()
    {
        return parent::getArrayCopy();
    }
}
