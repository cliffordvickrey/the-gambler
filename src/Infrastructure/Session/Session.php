<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Session;

use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;
use JsonSerializable;
use Serializable;
use UnexpectedValueException;
use function is_array;
use function serialize;
use function unserialize;

final class Session implements JsonSerializable, Serializable
{
    private $player;
    private $gameId;

    public function __construct(Player $player, ?GameId $gameId = null)
    {
        $this->player = $player;
        $this->gameId = $gameId;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return GameId|null
     */
    public function getGameId(): ?GameId
    {
        return $this->gameId;
    }

    public function setGameId(?GameId $gameId): void
    {
        $this->gameId = $gameId;
    }

    public function serialize()
    {
        return serialize(['player' => $this->player, 'gameId' => $this->gameId]);
    }

    public function jsonSerialize(): array
    {
        return ['player' => (string)$this->player, 'gameId' => (string)$this->gameId];
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => [Player::class, GameId::class]]);

        if (!is_array($unSerialized)) {
            throw new UnexpectedValueException('Expected array');
        }

        $player = $unSerialized['player'] ?? null;
        if (!($player instanceof Player)) {
            throw new UnexpectedValueException('Expected player');
        }

        $gameId = $unSerialized['gameId'] ?? null;
        if (!($gameId instanceof GameId)) {
            $gameId = null;
        }

        $this->player = $player;
        $this->gameId = $gameId;
    }
}
