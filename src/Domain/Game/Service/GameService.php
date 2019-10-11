<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Service;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveCardsLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveHandDealtLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveSkill;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use UnexpectedValueException;
use function floor;

class GameService implements GameServiceInterface
{
    private $handTypeResolver;
    private $probabilityService;
    private $rules;

    public function __construct(
        HandTypeResolverInterface $handTypeResolver,
        ProbabilityServiceInterface $probabilityService,
        RulesInterface $rules
    )
    {
        $this->handTypeResolver = $handTypeResolver;
        $this->probabilityService = $probabilityService;
        $this->rules = $rules;
    }

    public function getStartingPurse(): int
    {
        return $this->rules->getStartingPurse();
    }

    public function getDefaultBetAmount(): int
    {
        return $this->rules->getBetAmount();
    }

    public function analyzeMove(GameState $state, Draw $draw, int $betAmount): MoveAnalysis
    {
        $hand = $state->getHand();
        $handType = $state->getHandType();

        if (null === $hand || null === $handType) {
            throw new UnexpectedValueException('Could not resolve hand or hand type');
        }

        $payoutRatio = (float)($betAmount / $this->rules->getBetAmount());
        $payout = (int)floor($this->rules->getPayoutAmount($handType) * $payoutRatio);

        $tree = $this->probabilityService->getProbabilityTree($hand);
        $node = $tree->getNode($draw);
        $highestNode = $tree->getNodeWithHighestMeanPayout();

        $expectedPayout = $payoutRatio * $node->getMeanPayout();
        $optimalExpectedPayout = $payoutRatio * $highestNode->getMeanPayout();

        $skill = new MoveSkill($expectedPayout, $highestNode->getDraw(), $optimalExpectedPayout);

        $minExpected = $payoutRatio * $this->probabilityService->getMinHighestPayout();

        $logOptimalExpected = Math::logTransformScalar($optimalExpectedPayout, $minExpected);

        $logMeanOptimalExpected = $this->probabilityService->getLogMeanHighestPayout();
        $logOptimalStDev = $this->probabilityService->getLogStandardDeviationOfHighestPayout();

        $cardsLuck = new MoveCardsLuck(
            $optimalExpectedPayout,
            Math::standardize($logOptimalExpected, $logMeanOptimalExpected, $logOptimalStDev)
        );

        $logPayout = Math::logTransformScalar($payout, $payoutRatio * $node->getMinPayout());

        $logStDev = $node->getLogStandardDeviation();
        $zScore = null;
        if (0.0 !== $logStDev) {
            $zScore = Math::standardize(
                $logPayout,
                $node->getLogMeanPayout(),
                $node->getLogStandardDeviation()
            );
        }

        $handDealtLuck = new MoveHandDealtLuck($expectedPayout, $payout, $zScore);

        return new MoveAnalysis($skill, $cardsLuck, $handDealtLuck);
    }

    public function resolve(Hand $hand): HandType
    {
        return $this->handTypeResolver->resolve($hand);
    }

    public function getProbabilityTree(Hand $hand): ProbabilityTree
    {
        return $this->probabilityService->getProbabilityTree($hand);
    }
}
