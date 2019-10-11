<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Entity;

use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameException;
use Cliffordvickrey\TheGambler\Domain\Game\Service\GameServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameMeta;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use JsonSerializable;
use LogicException;

final class Game implements GameInterface, JsonSerializable
{
    private $id;
    private $gameService;
    private $meta;
    private $state;
    private $analysis;

    public function __construct(
        GameId $id,
        GameServiceInterface $gameService,
        ?GameMeta $meta = null,
        ?GameState $state = null,
        ?MoveAnalysis $analysis = null
    )
    {
        $this->id = $id;
        $this->gameService = $gameService;
        $this->meta = $meta ?? new GameMeta($gameService->getStartingPurse());
        $this->state = $state ?? new GameState();
        $this->analysis = $analysis;
    }

    public function __clone()
    {
        $this->meta = clone $this->meta;
        $this->state = clone $this->state;
        if (null !== $this->analysis) {
            $this->analysis = clone $this->analysis;
        }
    }

    /**
     * @param int|null $amount
     * @throws GameException
     */
    public function bet(?int $amount = null): void
    {
        $cheated = $this->meta->getCheated();
        $betAmount = $amount ?? $this->gameService->getDefaultBetAmount();

        if ($betAmount < 1) {
            throw new GameException('Bet amount cannot be less than $1.00');
        }

        $purse = $this->meta->getPurse();

        if (!$cheated && $betAmount > $purse) {
            throw new GameException('Cannot deal cards; not enough funds for bet');
        }

        $this->state->deal($betAmount);
        $this->meta->bet($betAmount);
        $this->analysis = null;
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
        $hand = $this->state->getHand();

        if (null === $hand) {
            throw new LogicException('Cannot play; no cards dealt');
        }

        $this->state->play($draw, $this->gameService);
        $betAmount = $this->state->getBetAmount();

        if (null === $betAmount) {
            throw new LogicException('Cannot play; no bet');
        }

        $this->analysis = $this->gameService->analyzeMove(
            $this->state,
            $draw,
            $betAmount
        );

        $this->meta->addToPurse(
            $this->analysis->getHandDealtLuck()->getActualPayout(),
            $this->analysis->getSkill()->getEfficiency(),
            $this->analysis->getCardsLuck()->getPercentile()
        );
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
        if (null !== $hand && ($cheated || null !== $this->state->getHandType())) {
            $probabilityTree = $this->gameService->getProbabilityTree($hand);
        }

        return [
            'gameId' => $this->id,
            'meta' => $this->meta,
            'state' => $this->state,
            'probability' => $probabilityTree,
            'analysis' => $this->analysis
        ];
    }

    /**
     * @return MoveAnalysis|null
     */
    public function getAnalysis(): ?MoveAnalysis
    {
        return $this->analysis;
    }
}
