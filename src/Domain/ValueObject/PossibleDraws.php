<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Collection\AbstractDrawCollection;

final class PossibleDraws extends AbstractDrawCollection
{
    public function __construct()
    {
        for ($i = 1; $i <= 32; $i++) {
            $this->draws[] = Draw::fromId($i);
        }
    }
}
