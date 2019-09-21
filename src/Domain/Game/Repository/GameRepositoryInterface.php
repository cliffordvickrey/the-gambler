<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameNotFoundException;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;

interface GameRepositoryInterface
{
    /**
     * @param GameId $id
     * @return GameInterface
     * @throws GameNotFoundException
     */
    public function get(GameId $id): GameInterface;

    public function getNew(): GameInterface;

    public function has(GameId $id): bool;

    public function save(GameInterface $game): void;

    public function delete(GameInterface $game): void;
}
