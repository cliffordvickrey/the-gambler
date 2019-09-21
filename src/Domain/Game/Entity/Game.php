<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Entity;

use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameException;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameMeta;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use JsonSerializable;

final class Game implements GameInterface, JsonSerializable
{
    private $id;
    private $rules;
    private $handTypeResolver;
    private $probabilityService;
    private $meta;
    private $state;

    public function __construct(
        GameId $id,
        RulesInterface $rules,
        HandTypeResolverInterface $handTypeResolver,
        ProbabilityServiceInterface $probabilityService,
        ?GameMeta $meta = null,
        ?GameState $state = null
    )
    {
        $this->id = $id;
        $this->rules = $rules;
        $this->handTypeResolver = $handTypeResolver;
        $this->probabilityService = $probabilityService;
        $this->meta = $meta ?? new GameMeta($rules->getStartingPurse());
        $this->state = $state ?? new GameState();
    }

    public function __clone()
    {
        $this->meta = clone $this->meta;
        $this->state = clone $this->state;
    }

    /**
     * @param int|null $amount
     * @throws GameException
     */
    public function bet(?int $amount = null): void
    {
        $cheated = $this->meta->getCheated();
        $betAmount = $amount ?? $this->rules->getBetAmount();

        if ($betAmount < 1) {
            throw new GameException('Bet amount cannot be less than $1.00');
        }

        $purse = $this->meta->getPurse();

        if (!$cheated && $betAmount > $purse) {
            throw new GameException('Cannot deal cards; not enough funds for bet');
        }

        $this->state->deal($betAmount);
        $this->meta->bet($betAmount);
    }

    public function cheat(): void
    {
        $this->meta->cheat();
    }

    /**
     * @param Draw $draw
     * @throws GameException
     */
    public function play(Draw $draw): void
    {
        $this->state->play($draw, $this->handTypeResolver);
        $betAmount = $this->state->getBetAmount();
        $payoutRatio = (float)($betAmount / $this->rules->getBetAmount());

        $hand = $this->state->getHand();
        $handType = $this->state->getHandType();
        $payout = (int)floor($this->rules->getPayoutAmount($handType) * $payoutRatio);
        $moveAnalysis = $this->analyzeMove($hand, $draw, $payoutRatio);

        $this->meta->addToPurse($payout, $moveAnalysis);
    }

    private function analyzeMove(Hand $hand, Draw $draw, float $payoutRatio): MoveAnalysis
    {
        $tree = $this->probabilityService->getProbabilityTree($hand);
        $node = $tree->getNode($draw);
        $highestNode = $tree->getNodeWithHighestMeanPayout();

        $expectedAmount = $payoutRatio * $node->getMeanPayout();
        $maxExpectedAmount = $payoutRatio * $highestNode->getMeanPayout();
        $meanMaxExpectedAmount = $payoutRatio * $this->probabilityService->getMeanHighestPayout();

        return new MoveAnalysis($expectedAmount, $maxExpectedAmount, $meanMaxExpectedAmount);
    }

    /**
     * @param int $offset
     * @param Card $card
     * @throws GameException
     */
    public function spliceHand(int $offset, Card $card): void
    {
        $cheated = $this->meta->getCheated();
        if (!$cheated) {
            throw new GameException('Cannot modify the hand; player must cheat first');
        }

        $this->state->splice($offset, $card);
    }

    /**
     * @return GameMeta
     */
    public function getMeta(): GameMeta
    {
        return $this->meta;
    }

    /**
     * @return GameState
     */
    public function getState(): GameState
    {
        return $this->state;
    }

    /**
     * @return GameId
     */
    public function getId(): GameId
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        $probabilityTree = null;

        $cheated = $this->meta->getCheated();
        $hand = $this->state->getHand();
        if ($cheated && null !== $hand) {
            $probabilityTree = $this->probabilityService->getProbabilityTree($hand);
        }

        return [
            'gameId' => $this->id,
            'meta' => $this->meta,
            'state' => $this->state,
            'probability' => $probabilityTree
        ];
    }
}
