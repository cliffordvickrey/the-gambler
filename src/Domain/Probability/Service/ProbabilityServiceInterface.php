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
}