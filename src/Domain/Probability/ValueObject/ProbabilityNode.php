<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use InvalidArgumentException;
use UnexpectedValueException;
use function array_combine;
use function array_keys;
use function array_map;
use function array_sum;
use function is_array;
use function is_float;
use function is_string;
use function serialize;
use function sprintf;
use function unserialize;

class ProbabilityNode implements PortableInterface
{
    private $draw;
    private $frequencies;
    private $percentages;
    private $rules;
    private $meanPayout;
    private $meanPayoutDollarAmount;

    public function __construct(
        Draw $draw,
        array $frequencies,
        ?RulesInterface $rules,
        ?float $meanPayout = null
    )
    {
        if (null === $rules && null === $meanPayout) {
            throw new InvalidArgumentException('At least one of $rules and $meanPayout must be non-null');
        }

        $this->draw = $draw;
        $this->frequencies = $frequencies;
        $this->rules = $rules;
        $this->meanPayout = $meanPayout;
    }

    public function __clone()
    {
        $this->draw = clone $this->draw;
        if (null !== $this->rules) {
            $this->rules = clone $this->rules;
        }
    }

    public function withDraw(Draw $draw): ProbabilityNode
    {
        $static = clone $this;
        $static->draw = $draw;
        return $static;
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => [Draw::class]]);

        $draw = $unSerialized['draw'] ?? null;
        $frequencies = $unSerialized['frequencies'] ?? null;
        $percentages = $unSerialized['percentages'] ?? null;
        $meanPayout = $unSerialized['meanPayout'] ?? null;
        $meanPayoutDollarAmount = $unSerialized['meanPayoutDollarAmount'] ?? null;

        if (!($draw instanceof Draw)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', $draw));
        }

        if (!is_array($frequencies)) {
            throw new UnexpectedValueException('Expected array');
        }

        if (!is_float($meanPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $this->draw = $draw;
        $this->frequencies = $frequencies;
        $this->meanPayout = $meanPayout;

        if (is_array($percentages)) {
            $this->percentages = $percentages;
        }

        if (is_string($meanPayoutDollarAmount)) {
            $this->meanPayoutDollarAmount = $meanPayoutDollarAmount;
        }
    }

    public function getDraw(): Draw
    {
        return $this->draw;
    }

    public function getFrequencies(): array
    {
        return $this->frequencies;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize(
            [
                'draw' => $this->draw,
                'frequencies' => $this->frequencies,
                'percentages' => $this->getPercentages(),
                'meanPayout' => $this->getMeanPayout(),
                'meanPayoutDollarAmount' => $this->getMeanPayoutDollarAmount()
            ]
        );
    }

    public function getPercentages(): array
    {
        if (null !== $this->percentages) {
            return $this->percentages;
        }

        $count = array_sum($this->frequencies);

        $percentagesByHand = array_map(function (int $frequency) use ($count): string {
            return Format::percentFormat($frequency / $count);
        }, $this->frequencies);

        $percentages = array_combine(array_keys($this->frequencies), $percentagesByHand);
        $this->percentages = $percentages;

        return $this->percentages;
    }

    public function getMeanPayout(): float
    {
        if (null !== $this->meanPayout) {
            return $this->meanPayout;
        }

        $handTypes = array_map(function (string $handTypeScalar): HandType {
            return new HandType($handTypeScalar);
        }, array_keys($this->frequencies));

        $sumByHand = array_map(function (HandType $handType, int $frequency): float {
            return (float)($frequency * $this->rules->getPayoutAmount($handType));
        }, $handTypes, $this->frequencies);

        $sum = array_sum($sumByHand);
        $count = array_sum($this->frequencies);

        return (float)($sum / $count);
    }

    /**
     * @return string
     */
    public function getMeanPayoutDollarAmount(): string
    {
        if (null !== $this->meanPayoutDollarAmount) {
            return $this->meanPayoutDollarAmount;
        }

        $meanPayout = $this->getMeanPayout();
        $meanPayoutDollarAmount = Format::dollarFormat($meanPayout);
        $this->meanPayoutDollarAmount = $meanPayoutDollarAmount;
        return $meanPayoutDollarAmount;
    }

    public function jsonSerialize()
    {
        return [
            'draw' => $this->draw,
            'frequencies' => $this->frequencies,
            'percentages' => $this->getPercentages(),
            'meanPayout' => $this->getMeanPayoutDollarAmount()
        ];
    }
}
