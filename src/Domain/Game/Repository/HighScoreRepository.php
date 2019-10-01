<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Game\Collection\HighScores;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\HighScore;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;
use DateTimeImmutable;
use DateTimeZone;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use function array_filter;
use function array_merge;
use function array_slice;
use function count;
use function is_bool;
use function sprintf;
use const ARRAY_FILTER_USE_BOTH;

class HighScoreRepository implements HighScoresRepositoryInterface
{
    const CACHE_KEY = 'high-scores';
    const DEFAULT_MAX_SCORES = 10;

    private $cache;
    private $maxScores;

    public function __construct(CacheInterface $cache, ?int $maxScores = null)
    {
        $this->cache = $cache;
        $this->maxScores = $maxScores ?? self::DEFAULT_MAX_SCORES;
    }

    public function add(Player $player, GameInterface $game): void
    {
        $meta = $game->getMeta();
        if ($meta->getCheated()) {
            return;
        }

        $highScores = $this->get();
        $score = $meta->getScore();
        $rank = $highScores->getRank($score);
        $index = $rank - 1;
        if ($rank > self::DEFAULT_MAX_SCORES) {
            return;
        }

        $dateTime = DateTimeImmutable::createFromFormat('0.u00 U', microtime());
        if (is_bool($dateTime)) {
            throw new RuntimeException('There was an internal PHP DateTime error');
        }
        $dateTime = $dateTime->setTimezone(new DateTimeZone('EST'));
        $date = $dateTime->format('m-d-Y');

        $newHighScore = new HighScore(
            $player,
            $date,
            $game->getId(),
            $meta
        );

        $iterator = $highScores->getIterator();
        $copy = $iterator->getArrayCopy();
        $part1 = array_slice($copy, 0, $index);
        $part2 = array_slice($copy, $index);
        $copy = array_merge($part1, [$newHighScore], $part2);

        $gameId = (string)$game->getId();
        $copy = array_filter($copy, function (HighScore $highScore, $key) use ($gameId, $index): bool {
            return !($key !== $index && $gameId === (string)$highScore->getGameId());
        }, ARRAY_FILTER_USE_BOTH);

        if (count($copy) > self::DEFAULT_MAX_SCORES) {
            $copy = array_slice($copy, 0, self::DEFAULT_MAX_SCORES);
        }

        $newHighScores = new HighScores(...$copy);
        $this->save($newHighScores);
    }

    public function get(): HighScores
    {
        try {
            $cached = $this->cache->get(self::CACHE_KEY);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if ($cached instanceof HighScores) {
            return $cached;
        }

        return new HighScores();
    }

    private function save(HighScores $highScores): void
    {
        try {
            $this->cache->set(self::CACHE_KEY, $highScores);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }
}
