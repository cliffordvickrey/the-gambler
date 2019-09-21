<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Enum;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use function is_string;
use function serialize;
use function unserialize;

class HandType extends AbstractEnum implements PortableInterface
{
    const NOTHING = 'nothing';
    const JACKS_OR_BETTER = 'jacksOrBetter';
    const TWO_PAIR = 'twoPair';
    const THREE_OF_A_KIND = 'threeOfAKind';
    const STRAIGHT = 'straight';
    const FLUSH = 'flush';
    const FULL_HOUSE = 'fullHouse';
    const FOUR_OF_A_KIND = 'fourOfAKind';
    const STRAIGHT_FLUSH = 'straightFlush';
    const ROYAL_FLUSH = 'royalFlush';

    protected static $enum = [
        self::NOTHING => self::NOTHING,
        self::JACKS_OR_BETTER => self::JACKS_OR_BETTER,
        self::TWO_PAIR => self::TWO_PAIR,
        self::THREE_OF_A_KIND => self::THREE_OF_A_KIND,
        self::STRAIGHT => self::STRAIGHT,
        self::FLUSH => self::FLUSH,
        self::FULL_HOUSE => self::FULL_HOUSE,
        self::FOUR_OF_A_KIND => self::FOUR_OF_A_KIND,
        self::STRAIGHT_FLUSH => self::STRAIGHT_FLUSH,
        self::ROYAL_FLUSH => self::ROYAL_FLUSH
    ];

    private static $descriptions = [
        self::NOTHING => 'Nothing',
        self::JACKS_OR_BETTER => 'Jacks or Better',
        self::TWO_PAIR => 'Two Pair',
        self::THREE_OF_A_KIND => 'Three of a Kind',
        self::STRAIGHT => 'Straight',
        self::FLUSH => 'Flush',
        self::FULL_HOUSE => 'Full House',
        self::FOUR_OF_A_KIND => 'Four of a Kind',
        self::STRAIGHT_FLUSH => 'Straight Flush',
        self::ROYAL_FLUSH => 'Royal Flush'
    ];

    public static function getDescriptions(): array
    {
        return self::$descriptions;
    }

    public function serialize()
    {
        return serialize($this->value);
    }

    public function unserialize($serialized)
    {
        $value = unserialize($serialized, ['allowed_classes' => false]);
        if (!is_string($value)) {
            $value = '';
        }
        $static = new static($value);
        $this->value = $static->value;;
    }

    public function jsonSerialize(): string
    {
        return (string)$this->value;
    }
}
