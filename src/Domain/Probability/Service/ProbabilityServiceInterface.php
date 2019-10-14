<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\Service;

use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;

interface ProbabilityServiceInterface
{
    public function getCanonicalProbabilityTree(Hand $hand): ProbabilityTree;

    public function getLogMeanHighestPayout(): float;

    public function getLogStandardDeviationOfHighestPayout(): float;

    public function getMeanHighestPayout(): float;

    public function getMinHighestPayout(): float;

    public function getProbabilityTree(Hand $hand): ProbabilityTree;

    public function getRootProbabilityNode(): ProbabilityNode;

    public function getStandardDeviationOfHighestPayout(): float;
}