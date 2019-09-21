<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Collection;

use Cliffordvickrey\TheGambler\Domain\Contract\CollectionInterface;
use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\HighScore;
use UnexpectedValueException;
use function array_map;
use function array_multisort;
use function count;
use function is_array;
use function serialize;
use function unserialize;
use const SORT_DESC;
use const SORT_NUMERIC;

final class HighScores implements CollectionInterface, PortableInterface
{
    /** @var HighScore[] */
    private $highScores;

    public function __construct(HighScore...$highScores)
    {
        $scores = array_map(function (HighScore $highScore): int {
            return $highScore->getMeta()->getScore();
        }, $highScores);

        array_multisort($scores, SORT_DESC, SORT_NUMERIC, $highScores);
        $this->highScores = $highScores;
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, [HighScore::class]);
        if (!is_array($unSerialized)) {
            throw new UnexpectedValueException('Expected array');
        }
        $static = new static(...$unSerialized);
        $this->highScores = $static->highScores;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize($this->highScores);
    }

    public function jsonSerialize()
    {
        return $this->highScores;
    }

    public function getRank(int $score): int
    {
        foreach ($this->highScores as $i => $highScore) {
            $meta = $highScore->getMeta();
            $scoreToTest = $meta->getScore();
            if ($score > $scoreToTest) {
                return $i + 1;
            }
        }

        return count($this->highScores) + 1;
    }

    /**
     * @return int[]
     */
    public function getHighScores(): array
    {
        return array_map(function (HighScore $highScore): int {
            return $highScore->getMeta()->getScore();
        }, $this->highScores);
    }

    public function getIterator(): HighScoresIterator
    {
        return new HighScoresIterator($this->highScores);
    }

    public function count(): int
    {
        return count($this->highScores);
    }
}
