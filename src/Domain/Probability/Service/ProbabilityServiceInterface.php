<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\Service;

use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;

interface ProbabilityServiceInterface
{
    public function getCanonicalProbabilityTree(Hand $hand): ProbabilityTree;

    public function getProbabilityTree(Hand $hand): ProbabilityTree;

    public function getMeanHighestPayout(): float;

    public function getMinHighestPayout(): float;

    public function getStandardDeviationOfHighestPayout(): float;

    public function getLogMeanHighestPayout(): float;

    public function getLogStandardDeviationOfHighestPayout(): float;

}