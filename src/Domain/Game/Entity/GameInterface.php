<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Entity;

use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameMeta;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;

interface GameInterface
{
    public function bet(?int $amount = null): void;

    public function cheat(): void;

    public function play(Draw $draw): void;

    public function spliceHand(int $offset, Card $card): void;

    public function getId(): GameId;

    public function getMeta(): GameMeta;

    public function getState(): GameState;
}