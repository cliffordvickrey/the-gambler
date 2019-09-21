<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Rules;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;

interface RulesInterface
{
    public function getBetAmount(): int;

    public function getStartingPurse(): int;

    public function getPayoutAmount(HandType $handType): int;

    public function toArray(): array;
}
