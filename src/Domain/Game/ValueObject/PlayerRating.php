<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use JsonSerializable;

final class PlayerRating implements JsonSerializable
{
    private $value;

    public function __construct(int $score)
    {
        $this->value = self::resolveValue($score);
    }

    private static function resolveValue(int $score): string
    {
        switch (true) {
            case $score < 250:
                return 'Tim Donaghy';
            case $score < 500:
                return 'Davey Scatino';
            case $score < 750:
                return 'Brian Molony';
            case $score < 1000:
                return 'Morrie Kesseler';
            case $score < 1500:
                return 'Earl of Sandwich';
            case $score < 3000:
                return 'Nick Dandolos';
            case $score < 5000:
                return 'Mike McDermott ';
            case $score < 7500:
                return 'Ace Rothstein';
            case $score < 10000:
                return 'Data the Android';
            default:
                return 'Kenny Rogers (The Gambler himself!)';
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}