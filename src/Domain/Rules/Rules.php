<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Rules;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use InvalidArgumentException;
use UnexpectedValueException;
use function array_combine;
use function array_keys;
use function array_map;
use function is_array;
use function is_int;
use function serialize;
use function sprintf;
use function unserialize;

final class Rules implements RulesInterface, PortableInterface
{
    const DEFAULT_BET_AMOUNT = 5;
    const DEFAULT_STARTING_PURSE = 200;
    const DEFAULT_PAYOUT_NOTHING = 0;
    const DEFAULT_PAYOUT_JACKS_OR_BETTER = 5;
    const DEFAULT_PAYOUT_TWO_PAIR = 10;
    const DEFAULT_PAYOUT_THREE_OF_A_KIND = 15;
    const DEFAULT_PAYOUT_STRAIGHT = 20;
    const DEFAULT_PAYOUT_FLUSH = 25;
    const DEFAULT_PAYOUT_FULL_HOUSE = 40;
    const DEFAULT_PAYOUT_FOUR_OF_A_KIND = 125;
    const DEFAULT_PAYOUT_STRAIGHT_FLUSH = 250;
    const DEFAULT_PAYOUT_ROYAL_FLUSH = 5000;

    private $betAmount;
    private $startingPurse;
    private $handPayouts;

    public function __construct(int $betAmount, int $startingPurse, array $handPayouts)
    {
        if ($betAmount < 0) {
            throw new InvalidArgumentException('Bet amount must not be negative');
        }

        if ($startingPurse < $betAmount) {
            throw new InvalidArgumentException('Starting purse cannot be less than bet amount');
        }

        $handPayouts = self::getValidPayouts($handPayouts);

        $this->betAmount = $betAmount;
        $this->startingPurse = $startingPurse;
        $this->handPayouts = $handPayouts;
    }

    private static function getValidPayouts(array $handPayouts): array
    {
        $handTypes = HandType::getEnum();

        $laggedHandType = 'none';
        $minimum = -1;
        $validPayouts = [];

        foreach ($handTypes as $handType) {
            self::assertValidHandPayout($handType, $handPayouts[$handType] ?? null, $laggedHandType, $minimum);
            $validPayouts[$handType] = $handPayouts[$handType];
        }

        return $handPayouts;
    }

    private static function assertValidHandPayout(
        string $handType, $payout, string &$laggedHandType, int &$minimum
    ): void
    {
        if (null === $payout) {
            throw new InvalidArgumentException(sprintf('No hand payout amount provided for %s', $handType));
        }

        if (!is_int($payout)) {
            throw new InvalidArgumentException(sprintf('Hand payout for %s must be numeric', $handType));
        }

        if ($payout < 0) {
            throw new InvalidArgumentException(sprintf('Hand payout for %s cannot be negative', $handType));
        }

        if ($payout <= $minimum) {
            throw new InvalidArgumentException(
                sprintf('Hand payout for %s must be greater than hand payout for %s', $handType, $laggedHandType)
            );
        }

        $laggedHandType = $handType;
        $minimum = $payout;
    }

    public static function fromDefaults(): self
    {
        return new static(self::DEFAULT_BET_AMOUNT, self::DEFAULT_STARTING_PURSE, self::getDefaultHandPayouts());
    }

    public static function getDefaultHandPayouts(): array
    {
        return [
            HandType::NOTHING => self::DEFAULT_PAYOUT_NOTHING,
            HandType::JACKS_OR_BETTER => self::DEFAULT_PAYOUT_JACKS_OR_BETTER,
            HandType::TWO_PAIR => self::DEFAULT_PAYOUT_TWO_PAIR,
            HandType::THREE_OF_A_KIND => self::DEFAULT_PAYOUT_THREE_OF_A_KIND,
            HandType::STRAIGHT => self::DEFAULT_PAYOUT_STRAIGHT,
            HandType::FLUSH => self::DEFAULT_PAYOUT_FLUSH,
            HandType::FULL_HOUSE => self::DEFAULT_PAYOUT_FULL_HOUSE,
            HandType::FOUR_OF_A_KIND => self::DEFAULT_PAYOUT_FOUR_OF_A_KIND,
            HandType::STRAIGHT_FLUSH => self::DEFAULT_PAYOUT_STRAIGHT_FLUSH,
            HandType::ROYAL_FLUSH => self::DEFAULT_PAYOUT_ROYAL_FLUSH
        ];
    }

    /**
     * @return int
     */
    public function getBetAmount(): int
    {
        return $this->betAmount;
    }

    public function getPayoutAmount(HandType $handType): int
    {
        return $this->handPayouts[(string)$handType];
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);
        $betAmount = $unSerialized['betAmount'] ?? null;
        $startingPurse = $unSerialized['startingPurse'] ?? null;
        $handPayouts = $unSerialized['handPayouts'] ?? null;

        if (!is_int($betAmount)) {
            throw new UnexpectedValueException('Expected integer');
        }

        if (!is_int($startingPurse)) {
            throw new UnexpectedValueException('Expected integer');
        }

        if (!is_array($handPayouts)) {
            throw new UnexpectedValueException('Expected array');
        }

        $static = new static($betAmount, $startingPurse, $handPayouts);
        $this->betAmount = $static->betAmount;
        $this->startingPurse = $static->startingPurse;
        $this->handPayouts = $static->handPayouts;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    public function jsonSerialize()
    {
        $handPayouts = array_combine(
            array_keys($this->handPayouts),
            array_map(function (int $payout): string {
                return Format::dollarFormat($payout);
            }, $this->handPayouts)
        );

        return [
            'betAmount' => Format::dollarFormat($this->betAmount),
            'startingPurse' => Format::dollarFormat($this->startingPurse),
            'handPayouts' => $handPayouts
        ];
    }

    public function toArray(): array
    {
        return [
            'betAmount' => $this->betAmount,
            'startingPurse' => $this->startingPurse,
            'handPayouts' => $this->handPayouts
        ];
    }

    public function getStartingPurse(): int
    {
        return $this->startingPurse;
    }
}
