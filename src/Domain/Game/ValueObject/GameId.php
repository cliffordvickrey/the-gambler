<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use function bin2hex;
use function preg_replace;
use function random_bytes;
use function serialize;
use function sprintf;
use function strlen;
use function unserialize;

final class GameId implements PortableInterface
{
    private $id;

    public function __construct(string $id)
    {
        if (32 !== strlen($id)) {
            throw new InvalidArgumentException('Invalid game ID length');
        }

        $id = preg_replace('/[^a-z0-9]/i', '', $id);

        if (32 !== strlen($id)) {
            throw new InvalidArgumentException('Game ID contains invalid characters');
        }

        $this->id = $id;
    }

    public static function generate()
    {
        try {
            $id = bin2hex(random_bytes(16));
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Error generating game ID: %s', $e->getMessage()));
        }

        return new static($id);
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function serialize()
    {
        return serialize($this->id);
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized);
        $static = new static($unSerialized);
        $this->id = $static->id;
    }

    public function jsonSerialize()
    {
        return $this->id;
    }
}