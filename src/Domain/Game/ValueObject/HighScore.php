<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use UnexpectedValueException;
use function is_array;
use function is_string;
use function serialize;
use function sprintf;
use function unserialize;

final class HighScore implements PortableInterface
{
    private $player;
    private $date;
    private $gameId;
    private $meta;

    public function __construct(Player $player, string $date, GameId $gameId, GameMeta $meta)
    {
        $this->player = $player;
        $this->date = $date;
        $this->gameId = $gameId;
        $this->meta = $meta;
    }

    public function getGameId(): GameId
    {
        return $this->gameId;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getMeta(): GameMeta
    {
        return $this->meta;
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, [
            'allowed_classes' => [Player::class, GameId::class, GameMeta::class]
        ]);

        if (!is_array($unSerialized)) {
            throw new UnexpectedValueException('Expected array');
        }

        $player = $unSerialized['player'] ?? null;
        if (!($player instanceof Player)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', Player::class));
        }

        $date = $unSerialized['date'] ?? null;
        if (!is_string($date)) {
            throw new UnexpectedValueException('Expected string');
        }

        $gameId = $unSerialized['gameId'] ?? null;
        if (!($gameId instanceof GameId)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', GameId::class));
        }

        $meta = $unSerialized['meta'] ?? null;
        if (!($meta instanceof GameMeta)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', GameMeta::class));
        }

        $this->player = $player;
        $this->date = $date;
        $this->gameId = $gameId;
        $this->meta = $meta;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize([
            'player' => $this->player,
            'date' => $this->date,
            'gameId' => $this->gameId,
            'meta' => $this->meta
        ]);
    }

    public function jsonSerialize()
    {
        return [
            'player' => $this->player,
            'date' => $this->date,
            'meta' => $this->meta
        ];
    }

    public function getDate(): string
    {
        return $this->date;
    }
}