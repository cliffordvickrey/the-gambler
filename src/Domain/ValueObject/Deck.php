<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Collection\AbstractCardCollection;

final class Deck extends AbstractCardCollection
{
    public function __construct()
    {
        for ($i = Card::MIN_ID; $i <= Card::MAX_ID; $i++) {
            $this->cards[] = Card::fromId($i);
        }
    }
}
