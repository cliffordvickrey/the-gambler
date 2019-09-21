<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Collection;

use ArrayIterator;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\HighScore;

class HighScoresIterator extends ArrayIterator
{
    /**
     * @return HighScore[]
     */
    public function getArrayCopy()
    {
        return parent::getArrayCopy();
    }

    public function current(): HighScore
    {
        return parent::current();
    }
}