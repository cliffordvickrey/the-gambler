<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\Contract\CollectionInterface;
use function count;

abstract class AbstractDrawCollection implements CollectionInterface
{
    /** @var Draw[] */
    protected $draws;

    public function count(): int
    {
        return count($this->draws);
    }

    /**
     * @return DrawIterator
     */
    public function getIterator(): DrawIterator
    {
        return new DrawIterator($this->draws);
    }
}
