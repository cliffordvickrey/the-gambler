<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use UnexpectedValueException;
use function is_float;
use function is_string;
use function serialize;
use function unserialize;

class MoveCardsLuck implements PortableInterface
{
    private $result;
    private $optimalExpectedPayout;
    private $zScore;

    public function __construct(string $result, float $optimalExpectedPayout, float $zScore)
    {
        $this->result = $result;
        $this->optimalExpectedPayout = $optimalExpectedPayout;
        $this->zScore = $zScore;
    }

    public function getResult(): string
    {
        return $this->result;
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
            'result' => $this->result,
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
            'result' => $this->result,
            'optimalExpectedPayout' => $this->optimalExpectedPayout,
            'zScore' => $this->zScore
        ]);
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);

        $result = $unSerialized['result'] ?? null;
        if (!is_string($result)) {
            throw new UnexpectedValueException('Expected string');
        }

        $optimalExpectedPayout = $unSerialized['optimalExpectedPayout'] ?? null;
        if (!is_float($optimalExpectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $zScore = $unSerialized['zScore'] ?? false;
        if (!is_float($optimalExpectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $this->result = $result;
        $this->optimalExpectedPayout = $optimalExpectedPayout;
        $this->zScore = $zScore;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }
}
