<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use RuntimeException;
use UnexpectedValueException;
use function array_combine;
use function array_keys;
use function array_map;
use function array_reduce;
use function array_sum;
use function is_array;
use function is_float;
use function is_string;
use function min;
use function serialize;
use function sprintf;
use function unserialize;

class ProbabilityNode implements PortableInterface
{
    private $draw;
    private $frequencies;
    private $percentages;
    private $percentagesRounded;
    private $rules;
    private $meanPayout;
    private $meanPayoutDollarAmount;
    private $meanPayoutDollarAmountRounded;
    private $minPayout;
    private $standardDeviation;
    private $logMeanPayout;
    private $logStandardDeviation;
    private $payoutFrequencies;

    public function __construct(
        Draw $draw,
        array $frequencies,
        RulesInterface $rules
    )
    {
        $this->draw = $draw;
        $this->frequencies = $frequencies;
        $this->rules = $rules;
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
        $percentagesRounded = $unSerialized['percentagesRounded'] ?? null;
        $meanPayout = $unSerialized['meanPayout'] ?? null;
        $meanPayoutDollarAmount = $unSerialized['meanPayoutDollarAmount'] ?? null;
        $meanPayoutDollarAmountRounded = $unSerialized['meanPayoutDollarAmountRounded'] ?? null;
        $minPayout = $unSerialized['minPayout'] ?? null;
        $standardDeviation = $unSerialized['standardDeviation'] ?? null;
        $logMeanPayout = $unSerialized['logMeanPayout'] ?? null;
        $logStandardDeviation = $unSerialized['logStandardDeviation'] ?? null;

        if (!($draw instanceof Draw)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', $draw));
        }

        if (!is_array($frequencies)) {
            throw new UnexpectedValueException('Expected array');
        }

        if (!is_float($meanPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        if (!is_float($minPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        if (!is_float($standardDeviation)) {
            throw new UnexpectedValueException('Expected float');
        }

        if (!is_float($logMeanPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        if (!is_float($logStandardDeviation)) {
            throw new UnexpectedValueException('Expected float');
        }

        $this->draw = $draw;
        $this->frequencies = $frequencies;
        $this->meanPayout = $meanPayout;
        $this->minPayout = $minPayout;

        if (is_array($percentages)) {
            $this->percentages = $percentages;
        }

        if (is_array($percentagesRounded)) {
            $this->percentagesRounded = $percentagesRounded;
        }

        if (is_string($meanPayoutDollarAmount)) {
            $this->meanPayoutDollarAmount = $meanPayoutDollarAmount;
        }

        if (is_string($meanPayoutDollarAmountRounded)) {
            $this->meanPayoutDollarAmountRounded = $meanPayoutDollarAmountRounded;
        }

        $this->standardDeviation = $standardDeviation;
        $this->logMeanPayout = $logMeanPayout;
        $this->logStandardDeviation = $logStandardDeviation;
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
                'percentagesRounded' => $this->getPercentagesRounded(),
                'meanPayout' => $this->getMeanPayout(),
                'meanPayoutDollarAmount' => $this->getMeanPayoutDollarAmount(),
                'meanPayoutDollarAmountRounded' => $this->getMeanPayoutDollarAmountRounded(),
                'minPayout' => $this->getMinPayout(),
                'standardDeviation' => $this->getStandardDeviation(),
                'logMeanPayout' => $this->getLogMeanPayout(),
                'logStandardDeviation' => $this->getLogStandardDeviation()
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

        $percentages = array_combine(array_keys($this->frequencies), $percentagesByHand) ?: [];
        $this->percentages = $percentages;

        return $this->percentages;
    }

    public function getPercentagesRounded(): array
    {
        if (null !== $this->percentagesRounded) {
            return $this->percentagesRounded;
        }

        $count = array_sum($this->frequencies);

        $percentagesRoundedByHand = array_map(function (int $frequency) use ($count): string {
            return Format::percentFormatRounded($frequency / $count);
        }, $this->frequencies);

        $percentagesRounded = array_combine(array_keys($this->frequencies), $percentagesRoundedByHand) ?: [];
        $this->percentagesRounded = $percentagesRounded;

        return $this->percentagesRounded;
    }

    public function getMeanPayout(): float
    {
        if (null !== $this->meanPayout) {
            return $this->meanPayout;
        }

        $this->meanPayout = Math::groupMean($this->getPayoutFrequencies());
        return $this->meanPayout;
    }

    private function getPayoutFrequencies(): array
    {
        if (null !== $this->payoutFrequencies) {
            return $this->payoutFrequencies;
        }

        $handTypes = array_map(function (string $handTypeScalar): HandType {
            return new HandType($handTypeScalar);
        }, array_keys($this->frequencies));

        $this->payoutFrequencies = array_reduce(
            $handTypes,
            function (array $carry, HandType $handType): array {
                if (null === $this->rules) {
                    throw new RuntimeException('Could not resolve rules');
                }

                $frequencies = $this->frequencies[(string)$handType];
                if ($frequencies > 0) {
                    $carry[$frequencies] = $this->rules->getPayoutAmount($handType);
                }
                return $carry;
            },
            []
        );

        return $this->payoutFrequencies;
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

    /**
     * @return string
     */
    public function getMeanPayoutDollarAmountRounded(): string
    {
        if (null !== $this->meanPayoutDollarAmountRounded) {
            return $this->meanPayoutDollarAmountRounded;
        }

        $meanPayout = $this->getMeanPayout();
        $meanPayoutDollarAmountRounded = Format::dollarFormat($meanPayout, 2);
        $this->meanPayoutDollarAmountRounded = $meanPayoutDollarAmountRounded;
        return $meanPayoutDollarAmountRounded;
    }

    public function getMinPayout(): float
    {
        if (null !== $this->minPayout) {
            return $this->minPayout;
        }

        $this->minPayout = min($this->getPayoutFrequencies());
        return $this->minPayout;
    }

    public function getStandardDeviation(): float
    {
        if (null !== $this->standardDeviation) {
            return $this->standardDeviation;
        }

        $this->standardDeviation = Math::groupStDev($this->getPayoutFrequencies());
        return $this->standardDeviation;
    }

    public function getLogMeanPayout(): float
    {
        if (null !== $this->logMeanPayout) {
            return $this->logMeanPayout;
        }

        $this->logMeanPayout = Math::groupMean(Math::logTransform($this->getPayoutFrequencies(), true));
        return $this->logMeanPayout;
    }

    public function getLogStandardDeviation(): float
    {
        if (null !== $this->logStandardDeviation) {
            return $this->logStandardDeviation;
        }

        $this->logStandardDeviation = Math::groupStDev(Math::logTransform($this->getPayoutFrequencies(), true));
        return $this->logStandardDeviation;
    }

    public function jsonSerialize()
    {
        return [
            'draw' => $this->draw,
            'frequencies' => $this->frequencies,
            'percentages' => $this->getPercentages(),
            'percentagesRounded' => $this->getPercentagesRounded(),
            'meanPayout' => $this->getMeanPayoutDollarAmount(),
            'meanPayoutRounded' => $this->getMeanPayoutDollarAmountRounded()
        ];
    }
}
