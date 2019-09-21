<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Collection;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use UnexpectedValueException;
use function array_map;
use function implode;
use function is_string;
use function serialize;
use function str_split;
use function unserialize;

class CardCollection extends AbstractCardCollection implements PortableInterface
{
    public function __construct(Card...$cards)
    {
        foreach ($cards as $card) {
            $this->cards[] = $card;
        }
    }

    public function serialize()
    {
        return serialize((string)$this);
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);

        if (!is_string($unSerialized)) {
            throw new UnexpectedValueException('Expected string');
        }

        if ('' === $unSerialized) {
            $this->cards = [];
            return;
        }

        $cardStrings = str_split($unSerialized, 2);
        $cards = [];
        foreach ($cardStrings as $cardString) {
            $cards[] = Card::fromId((int)$cardString);
        }
        $this->cards = $cards;
    }

    public function __toString(): string
    {
        return implode('', array_map('strval', $this->cards));
    }

    public function jsonSerialize()
    {
        return $this->cards;
    }
}