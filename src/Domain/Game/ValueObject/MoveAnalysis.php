<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

final class MoveAnalysis
{
    private $expectedAmount;
    private $maxExpectedAmount;
    private $meanMaxExpectedAmount;

    public function __construct(float $expectedAmount, float $maxExpectedAmount, float $meanMaxExpectedAmount)
    {
        $this->expectedAmount = $expectedAmount;
        $this->maxExpectedAmount = $maxExpectedAmount;
        $this->meanMaxExpectedAmount = $meanMaxExpectedAmount;
    }

    /**
     * @return float
     */
    public function getExpectedAmount(): float
    {
        return $this->expectedAmount;
    }

    /**
     * @return float
     */
    public function getMaxExpectedAmount(): float
    {
        return $this->maxExpectedAmount;
    }

    /**
     * @return float
     */
    public function getMeanMaxExpectedAmount(): float
    {
        return $this->meanMaxExpectedAmount;
    }
}

