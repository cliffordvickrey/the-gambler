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

    /** @var HandType[] */
    private $handTypeMemo = [];

    /** @var ProbabilityTree[] */
    private $probabilityTreeMemo = [];

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

        $normalPayout = $this->rules->getPayoutAmount($handType);

        $payoutRatio = (float)($betAmount / $this->rules->getBetAmount());
        $payout = (int)floor($normalPayout * $payoutRatio);

        $tree = $this->probabilityService->getProbabilityTree($hand);
        $node = $tree->getNode($draw);
        $highestNode = $tree->getNodeWithHighestMeanPayout();

        $normalExpectedPayout = $node->getMeanPayout();
        $normalOptimalExpectedPayout = $highestNode->getMeanPayout();

        $expectedPayout = $payoutRatio * $normalExpectedPayout;
        $optimalExpectedPayout = $payoutRatio * $normalOptimalExpectedPayout;

        $skill = new MoveSkill($expectedPayout, $highestNode->getDraw(), $optimalExpectedPayout);

        $minExpected = $this->probabilityService->getMinHighestPayout();
        $logOptimalExpected = Math::logTransformScalar($normalOptimalExpectedPayout, $minExpected);
        $logMeanOptimalExpected = $this->probabilityService->getLogMeanHighestPayout();
        $logOptimalStDev = $this->probabilityService->getLogStandardDeviationOfHighestPayout();

        $cardsLuck = new MoveCardsLuck(
            $optimalExpectedPayout,
            Math::standardize($logOptimalExpected, $logMeanOptimalExpected, $logOptimalStDev)
        );

        $logPayout = Math::logTransformScalar($normalPayout, $node->getMinPayout());

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
        $handScalar = (string)$hand;
        if (!isset($this->handTypeMemo[$handScalar])) {
            $this->handTypeMemo[$handScalar] = $this->handTypeResolver->resolve($hand);
        }

        return $this->handTypeMemo[$handScalar];
    }

    public function getProbabilityTree(Hand $hand): ProbabilityTree
    {
        $handScalar = (string)$hand;
        if (!isset($this->handTypeMemo[$handScalar])) {
            $this->probabilityTreeMemo[$handScalar] = $this->probabilityService->getProbabilityTree($hand);
        }

        return $this->probabilityTreeMemo[$handScalar];
    }
}
