<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Collection\CardCollection;
use function array_unique;
use function count;

final class Hand extends CardCollection
{
    const HAND_SIZE = 5;

    public function isValid(): bool
    {
        if (self::HAND_SIZE !== count($this->cards)) {
            return false;
        }

        if (count($this->cards) !== count(array_unique($this->cards))) {
            return false;
        }

        return true;
    }

}