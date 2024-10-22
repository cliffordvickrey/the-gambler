<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use UnexpectedValueException;
use function is_float;
use function is_int;
use function is_string;
use function serialize;
use function unserialize;

class MoveHandDealtLuck implements PortableInterface
{
    private $result;
    private $expectedPayout;
    private $actualPayout;
    private $zScore;

    public function __construct(string $result, float $expectedPayout, int $actualPayout, ?float $zScore)
    {
        $this->result = $result;
        $this->expectedPayout = $expectedPayout;
        $this->actualPayout = $actualPayout;
        $this->zScore = $zScore;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return float
     */
    public function getExpectedPayout(): float
    {
        return $this->expectedPayout;
    }

    /**
     * @return int
     */
    public function getActualPayout(): int
    {
        return $this->actualPayout;
    }

    /**
     * @return float|null
     */
    public function getZScore(): ?float
    {
        return $this->zScore;
    }

    public function jsonSerialize()
    {
        return [
            'result' => $this->result,
            'expectedPayout' => Format::dollarFormat($this->expectedPayout, 2),
            'actualPayout' => Format::dollarFormat($this->actualPayout, 2),
            'zScore' => null === $this->zScore ? 'N/A' : Format::numberFormat($this->zScore, 2),
            'percentile' => null === $this->zScore ? 'N/A' : Format::percentFormatRounded($this->getPercentile())
        ];
    }

    public function getPercentile(): float
    {
        if (null === $this->zScore) {
            return Math::percentile(1.0);
        }

        return Math::percentile($this->zScore);
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);

        $result = $unSerialized['result'] ?? null;
        if (!is_string($result)) {
            throw new UnexpectedValueException('Expected string');
        }

        $expectedPayout = $unSerialized['expectedPayout'] ?? false;
        if (!is_float($expectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $actualPayout = $unSerialized['actualPayout'] ?? false;
        if (!is_int($actualPayout)) {
            throw new UnexpectedValueException('Expected integer');
        }

        $zScore = $unSerialized['zScore'] ?? false;
        if (!is_float($zScore)) {
            $zScore = null;
        }

        $this->result = $result;
        $this->expectedPayout = $expectedPayout;
        $this->actualPayout = $actualPayout;
        $this->zScore = $zScore;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize([
            'result' => $this->result,
            'expectedPayout' => $this->expectedPayout,
            'actualPayout' => $this->actualPayout,
            'zScore' => $this->zScore
        ]);
    }
}
