<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use UnexpectedValueException;
use function is_float;
use function serialize;
use function unserialize;

class MoveCardsLuck implements PortableInterface
{
    private $optimalExpectedPayout;
    private $zScore;

    public function __construct(float $optimalExpectedPayout, float $zScore)
    {
        $this->optimalExpectedPayout = $optimalExpectedPayout;
        $this->zScore = $zScore;
    }

    /**
     * @return float
     */
    public function getOptimalExpectedPayout(): float
    {
        return $this->optimalExpectedPayout;
    }

    /**
     * @return float
     */
    public function getZScore(): float
    {
        return $this->zScore;
    }

    public function jsonSerialize()
    {
        return [
            'optimalExpectedPayout' => Format::dollarFormat($this->optimalExpectedPayout, 2),
            'zScore' => Format::numberFormat($this->zScore, 2),
            'percentile' => Format::percentFormatRounded($this->getPercentile())
        ];
    }

    public function getPercentile(): float
    {
        return Math::percentile($this->zScore);
    }

    public function serialize()
    {
        return serialize([
            'optimalExpectedPayout' => $this->optimalExpectedPayout,
            'zScore' => $this->zScore
        ]);
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);

        $optimalExpectedPayout = $unSerialized['optimalExpectedPayout'] ?? null;
        if (!is_float($optimalExpectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $zScore = $unSerialized['zScore'] ?? false;
        if (!is_float($optimalExpectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $this->optimalExpectedPayout = $optimalExpectedPayout;
        $this->zScore = $zScore;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }
}
