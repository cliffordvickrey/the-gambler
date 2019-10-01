<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameException;
use RuntimeException;
use function is_string;
use function preg_replace;
use function serialize;
use function strlen;
use function unserialize;

final class Player implements PortableInterface
{
    private $name;

    /**
     * Player constructor.
     * @param string $name
     * @throws GameException
     */
    public function __construct(string $name)
    {
        $name = preg_replace('/[^a-z0-9 ]/i', '', $name);

        if (!is_string($name)) {
            throw new RuntimeException('There was an internal regex error');
        }

        $strLen = strlen($name);
        if ($strLen < 1 || $strLen > 20) {
            throw new GameException('Invalid player name');
        }
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function serialize()
    {
        return serialize($this->name);
    }

    /**
     * @param string $serialized
     * @throws GameException
     */
    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => false]);
        $static = new static($unSerialized);
        $this->name = $static->name;
    }

    public function jsonSerialize()
    {
        return $this->name;
    }
}
