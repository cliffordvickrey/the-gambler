<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Contract\CollectionInterface;
use function array_pop;
use function count;

class HandTypeCollection implements CollectionInterface
{
    /** @var HandType[] */
    protected $handTypes = [];

    public function getIterator(): HandTypeIterator
    {
        return new HandTypeIterator($this->handTypes);
    }

    public function __clone()
    {
        foreach ($this->handTypes as $index => $handType) {
            $this->handTypes[$index] = clone $handType;
        }
    }

    public function count(): int
    {
        return count($this->handTypes);
    }

    public function add(HandType $handType): void
    {
        $this->handTypes[(string)$handType] = $handType;
    }

    public function has(HandType $handType): bool
    {
        return isset($this->handTypes[(string)$handType]);
    }

    public function pop(): HandType
    {
        if (0 === count($this->handTypes)) {
            return new HandType(HandType::NOTHING);
        }
        return array_pop($this->handTypes);
    }
}
