<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Enum\Suit;
use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use InvalidArgumentException;
use UnexpectedValueException;
use function floor;
use function is_int;
use function serialize;
use function sprintf;
use function unserialize;

final class Card implements PortableInterface
{
    const MIN_ID = 1;
    const MAX_ID = 52;

    private $rank;
    private $suit;

    public function __construct(Rank $rank, Suit $suit)
    {
        $this->rank = $rank;
        $this->suit = $suit;
    }

    public function __clone()
    {
        $this->rank = clone $this->rank;
        $this->suit = clone $this->suit;
    }

    public function serialize()
    {
        return serialize($this->getId());
    }

    public function getId(): int
    {
        return (($this->suit->getId() - 1) * Rank::MAX_RANK) + $this->rank->getValue();
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);

        if (!is_int($unSerialized)) {
            throw new UnexpectedValueException('Expected integer');
        }

        $static = Card::fromId($unSerialized);

        $this->rank = $static->rank;
        $this->suit = $static->suit;
    }

    public static function fromId(int $id): self
    {
        if ($id < self::MIN_ID || $id > self::MAX_ID) {
            throw new InvalidArgumentException(
                sprintf('Expected ID between %d and %d; got %d', self::MIN_ID, self::MAX_ID, $id)
            );
        }

        $rankScalar = ($id % Rank::MAX_RANK) ?: Rank::MAX_RANK;
        $suitScalar = (int)floor(($id - 1) / Rank::MAX_RANK) + 1;

        $rank = new Rank($rankScalar);
        $suit = Suit::fromId($suitScalar);

        return new static($rank, $suit);
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function __toString(): string
    {
        return sprintf('%02d', $this->getId());
    }

    public function jsonSerialize()
    {
        return $this->getId();
    }
}
