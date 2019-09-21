<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Collection\AbstractBoolCollection;
use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use InvalidArgumentException;
use OutOfBoundsException;
use UnexpectedValueException;
use function count;
use function floor;
use function is_int;
use function serialize;
use function sprintf;
use function unserialize;

final class Draw extends AbstractBoolCollection implements PortableInterface
{
    const MIN_ID = 1;
    const MAX_ID = 32;

    public function __construct(bool...$cardsToHold)
    {
        if (Hand::HAND_SIZE !== count($cardsToHold)) {
            throw new InvalidArgumentException(
                sprintf('Expected %d cards to hold; got %d', Hand::HAND_SIZE, count($cardsToHold))
            );
        }

        $this->values = $cardsToHold;
    }

    public function get(int $offset): bool
    {
        if (isset($this->values[$offset])) {
            return $this->values[$offset];
        }

        throw new OutOfBoundsException(sprintf('Invalid offset for %s, "%d"', static::class, $offset));
    }

    public function serialize()
    {
        return serialize($this->getId());
    }

    public function getId(): int
    {
        return ($this->values[0] * 16)
            + ($this->values[1] * 8)
            + ($this->values[2] * 4)
            + ($this->values[3] * 2)
            + $this->values[4]
            + 1;
    }

    public function unserialize($serialized)
    {
        $id = unserialize($serialized);
        if (!is_int($id)) {
            throw new UnexpectedValueException('Expected integer');
        }
        $static = static::fromId($id);
        $this->values = $static->values;
    }

    public static function fromId(int $id): Draw
    {
        if ($id < self::MIN_ID || $id > self::MAX_ID) {
            throw new InvalidArgumentException(sprintf('ID "%d" is out of range', $id));
        }

        $idToParse = $id - 1;
        $cardsToHold = self::parseId($idToParse, 16);
        return new static(...$cardsToHold);
    }

    private static function parseId(int &$id, int $divisor, array $carry = []): array
    {
        if (1 === $divisor) {
            $carry[] = 1 === $id;
            return $carry;
        }

        if (0 !== ($divisor % 2)) {
            throw new UnexpectedValueException('Expected an even number or one');
        }

        $hold = (bool)((int)floor($id / $divisor));
        $carry[] = $hold;
        if ($hold) {
            $id -= $divisor;
        }

        $divisor = (int)($divisor / 2);

        return self::parseId($id, $divisor, $carry);
    }

    public function __toString(): string
    {
        return sprintf('%02d', $this->getId());
    }

    public function jsonSerialize()
    {
        return $this->values;
    }
}