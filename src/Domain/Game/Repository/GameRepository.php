<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Game\Entity\Game;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameNotFoundException;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameMeta;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;
use function is_array;
use function sprintf;

class GameRepository implements GameRepositoryInterface
{
    private $cache;
    private $handTypeResolver;
    private $probabilityService;
    private $rules;
    private $ttl;

    public function __construct(
        CacheInterface $cache,
        HandTypeResolverInterface $handTypeResolver,
        ProbabilityServiceInterface $probabilityService,
        RulesInterface $rules,
        ?int $ttl = null
    )
    {
        $this->cache = $cache;
        $this->handTypeResolver = $handTypeResolver;
        $this->probabilityService = $probabilityService;
        $this->rules = $rules;
        $this->ttl = $ttl;
    }

    /**
     * @param GameId $id
     * @return GameInterface
     * @throws GameNotFoundException
     */
    public function get(GameId $id): GameInterface
    {
        try {
            $saved = $this->cache->get((string)$id);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if (null === $saved) {
            throw new GameNotFoundException('Game not found');
        }

        if (!is_array($saved)) {
            throw new UnexpectedValueException('Expected array');
        }

        $meta = $saved['meta'] ?? null;
        $state = $saved['state'] ?? null;

        if (!($meta instanceof GameMeta)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', GameMeta::class));
        }

        if (!($state instanceof GameState)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', GameState::class));
        }

        return new Game($id, $this->rules, $this->handTypeResolver, $this->probabilityService, $meta, $state);
    }

    public function has(GameId $id): bool
    {
        try {
            return $this->cache->has((string)$id);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }

    public function getNew(): GameInterface
    {
        return new Game(GameId::generate(), $this->rules, $this->handTypeResolver, $this->probabilityService);
    }

    public function save(GameInterface $game): void
    {
        $id = (string)$game->getId();

        $toSerialize = [
            'meta' => $game->getMeta(),
            'state' => $game->getState()
        ];

        try {
            $this->cache->set($id, $toSerialize, $this->ttl);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }

    public function delete(GameInterface $game): void
    {
        $id = (string)$game->getId();

        try {
            $this->cache->delete($id);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }
}
