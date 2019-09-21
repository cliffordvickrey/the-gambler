<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Game\Collection\HighScores;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;

interface HighScoresRepositoryInterface
{
    public function get(): HighScores;

    public function add(Player $player, GameInterface $game): void;
}