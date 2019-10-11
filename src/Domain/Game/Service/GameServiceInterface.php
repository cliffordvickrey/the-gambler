<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Service;

use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;

interface GameServiceInterface extends HandTypeResolverInterface
{
    public function getStartingPurse(): int;

    public function getDefaultBetAmount(): int;

    public function analyzeMove(GameState $state, Draw $draw, int $betAmount): MoveAnalysis;

    public function getProbabilityTree(Hand $hand): ProbabilityTree;
}
