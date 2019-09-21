<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use UnexpectedValueException;
use function is_bool;
use function is_float;
use function is_int;
use function serialize;
use function unserialize;

final class GameMeta implements PortableInterface
{
    private $turn;
    private $purse;
    private $highPurse;
    private $efficiency;
    private $luck;
    private $cheated;
    private $lastPayout;

    public function __construct(
        int $purse,
        int $turn = 0,
        int $highPurse = 0,
        float $efficiency = 0.0,
        float $luck = 0.0,
        bool $cheated = false,
        int $lastPayout = 0
    )
    {
        $this->purse = $purse;
        $this->turn = $turn;

        if ($purse > $highPurse) {
            $highPurse = $purse;
        }

        $this->highPurse = $highPurse;
        $this->efficiency = $efficiency;
        $this->luck = $luck;
        $this->cheated = $cheated;
        $this->lastPayout = $lastPayout;
    }

    public function getTurn(): int
    {
        return $this->turn;
    }

    public function getPurse(): int
    {
        return $this->purse;
    }

    public function getHighPurse(): int
    {
        return $this->highPurse;
    }

    public function getEfficiency(): float
    {
        return $this->efficiency;
    }

    public function getLuck(): float
    {
        return $this->luck;
    }

    public function bet(int $amount): void
    {
        $this->purse -= $amount;
    }

    public function addToPurse(int $amount, MoveAnalysis $analysis): void
    {
        // de-structure analysis
        $expectedAmount = $analysis->getExpectedAmount();
        $maxExpectedAmount = $analysis->getMaxExpectedAmount();
        $meanMaxExpectedAmount = $analysis->getMeanMaxExpectedAmount();

        // move efficiency: ratio of the move's expected payout vs. the maximum payout for this hand
        if (0 === $expectedAmount) {
            $numerator = $this->efficiency;
        } else {
            $moveEfficiency = self::safeDivision($expectedAmount, $maxExpectedAmount);
            $numerator = (($this->efficiency * $this->turn) + $moveEfficiency);
        }
        $this->efficiency = $numerator / ($this->turn + 1);

        // move luck: how good was the initial hand compared to every possible hand, and how good was the move's outcome
        // compared to the expected outcome?
        $moveLuck = (self::safeDivision($maxExpectedAmount, $meanMaxExpectedAmount) +
                self::safeDivision($amount, $expectedAmount)) / 2;
        $this->luck = (($this->luck * $this->turn) + $moveLuck) / ($this->turn + 1);

        $this->turn++;
        $this->purse += $amount;
        $this->lastPayout = $amount;

        if ($this->purse > $this->highPurse) {
            $this->highPurse = $this->purse;
        }
    }

    /**
     * @param float|int $dividend
     * @param float|int $divisor
     * @return float
     */
    private static function safeDivision($dividend, $divisor): float
    {
        if (0 === $divisor || 0.0 === $divisor) {
            return 1.0;
        }

        return (float)($dividend / $divisor);
    }

    public function jsonSerialize(): array
    {
        return [
            'turn' => $this->turn,
            'purse' => Format::dollarFormat($this->purse),
            'purseNumeric' => $this->purse,
            'highPurse' => Format::dollarFormat($this->highPurse),
            'efficiency' => Format::percentFormat($this->efficiency, 2),
            'luck' => Format::percentFormat($this->luck, 2),
            'score' => $this->getScore(),
            'cheated' => $this->cheated,
            'lastPayout' => Format::dollarFormat($this->lastPayout)
        ];
    }

    public function getScore(): int
    {
        return (int)floor($this->efficiency * $this->highPurse);
    }

    public function cheat(): void
    {
        $this->cheated = true;
    }

    public function getCheated(): bool
    {
        return $this->cheated;
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);

        $turn = $unSerialized['turn'] ?? null;
        $purse = $unSerialized['purse'] ?? null;
        $highPurse = $unSerialized['highPurse'] ?? null;
        $efficiency = $unSerialized['efficiency'] ?? null;
        $luck = $unSerialized['luck'] ?? null;
        $cheated = $unSerialized['cheated'] ?? null;
        $lastPayout = $unSerialized['lastPayout'] ?? null;

        if (!is_int($turn)) {
            throw new UnexpectedValueException('Expected integer');
        }

        if (!is_int($purse)) {
            throw new UnexpectedValueException('Expected integer');
        }

        if (!is_int($highPurse)) {
            $highPurse = 0;
        }

        if (!is_float($efficiency)) {
            $efficiency = 0.0;
        }

        if (!is_float($luck)) {
            $luck = 0.0;
        }

        if (!is_bool($cheated)) {
            $cheated = false;
        }

        if (!is_int($lastPayout)) {
            $lastPayout = 0;
        }

        $static = new static($purse, $turn, $highPurse, $efficiency, $luck, $cheated);
        $this->purse = $static->purse;
        $this->turn = $static->turn;
        $this->highPurse = $static->highPurse;
        $this->efficiency = $static->efficiency;
        $this->luck = $static->luck;
        $this->cheated = $static->cheated;
        $this->lastPayout = $lastPayout;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    private function toArray(): array
    {
        return [
            'turn' => $this->turn,
            'purse' => $this->purse,
            'highPurse' => $this->highPurse,
            'efficiency' => $this->efficiency,
            'luck' => $this->luck,
            'cheated' => $this->cheated,
            'lastPayout' => $this->lastPayout
        ];
    }
}
