<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\Service;

use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Generator\CombinationGenerator;
use Cliffordvickrey\TheGambler\Domain\Probability\Utility\CanonicalProbabilityTreeBuilder;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\PossibleDraws;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use function array_combine;
use function array_keys;
use function is_float;
use function md5;
use function range;
use function serialize;
use function sprintf;
use const PHP_SAPI;

class ProbabilityService implements ProbabilityServiceInterface
{
    const MEAN_PAYOUT_CACHE_KEY = 'meanPayout';

    private $cache;
    private $handTypeResolver;
    private $rules;
    private $treeBuilder;
    private $possibleDraws;

    public function __construct(
        CacheInterface $cache, HandTypeResolverInterface $handTypeResolver, RulesInterface $rules
    )
    {
        $this->cache = $cache;
        $this->handTypeResolver = $handTypeResolver;
        $this->rules = $rules;
        $this->treeBuilder = new CanonicalProbabilityTreeBuilder($handTypeResolver, $rules);
        $this->possibleDraws = new PossibleDraws();
    }

    public function getProbabilityTree(Hand $hand): ProbabilityTree
    {
        // get the probability tree of the canonical Poker hand associated with the unsorted hand
        $canonicalTree = $this->getCanonicalProbabilityTree($hand);

        // apply a canonical sort to the hand; get an array of [card ID (1 - 52) => sort order, ...]
        $handDecorator = new HandDecorator($hand);
        $canonicalHand = $handDecorator->getHandWithCanonicalSort();
        $canonicalKeys = array_combine(
            array_keys($canonicalHand->toIndexedArray()),
            range(0, Hand::HAND_SIZE - 1)
        );

        // map the card IDs of the unsorted hand to the sorted hand
        $cardIds = array_keys($hand->toIndexedArray());
        $keyMappings = [];
        foreach ($cardIds as $cardId) {
            $keyMappings[] = $canonicalKeys[$cardId];
        }

        // remap the cards held for each node to the unsorted hand
        $reMappedNodes = [];
        foreach ($this->possibleDraws as $draw) {
            $reMappedNodes[] = $this->getRemappedNode($canonicalTree, $draw, $keyMappings);
        }

        // build a new probability tee
        return new ProbabilityTree(...$reMappedNodes);
    }

    public function getCanonicalProbabilityTree(Hand $hand): ProbabilityTree
    {
        $cacheKey = $this->getCanonicalProbabilityTreeCacheKey($hand);
        $cached = $this->resolveCanonicalProbabilityTreeFromCache($cacheKey);
        if (null !== $cached) {
            return $cached;
        }

        self::assertCli();
        $tree = $this->treeBuilder->buildCanonicalProbabilityTree($hand);
        $this->persistCanonicalProbabilityTreeToCache($cacheKey, $tree);
        return $tree;
    }

    private function getCanonicalProbabilityTreeCacheKey(Hand $hand): string
    {
        $decorator = new HandDecorator($hand);
        $toSerialize = ['hash' => $decorator->getCanonicalHandHash(), 'rules' => $this->rules->toArray()];
        return md5(serialize($toSerialize));
    }

    private function resolveCanonicalProbabilityTreeFromCache(string $key): ?ProbabilityTree
    {
        try {
            $cached = $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a cache error: %s', $e->getMessage()));
        }

        if ($cached instanceof ProbabilityTree) {
            return $cached;
        }

        return null;
    }

    private static function assertCli(): void
    {
        if ('cli' !== PHP_SAPI) {
            throw new RuntimeException('PHP CLI only');
        }
    }

    private function persistCanonicalProbabilityTreeToCache(string $key, ProbabilityTree $probabilityTree): void
    {
        try {
            $this->cache->set($key, $probabilityTree);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a cache error: %s', $e->getMessage()));
        }
    }

    private function getRemappedNode(ProbabilityTree $canonicalTree, Draw $draw, array $keyMappings): ProbabilityNode
    {
        $node = $canonicalTree->getNode($draw);

        $sourceDraw = $draw->getIterator()->getArrayCopy();
        $targetDraw = [];

        foreach ($keyMappings as $key) {
            $targetDraw[] = $sourceDraw[$key];
        }

        $draw = new Draw(...$targetDraw);
        return $node->withDraw($draw);
    }

    public function getMeanHighestPayout(): float
    {
        $cached = $this->resolveMeanHighestPayoutFromCache();
        if (is_float($cached)) {
            return $cached;
        }

        self::assertCli();

        $deck = new Deck();
        $generator = (new CombinationGenerator())($deck, Hand::HAND_SIZE);

        $sum = 0.0;
        $memo = [];
        foreach ($generator as $cards) {
            $hand = new Hand(...$cards);
            $handDecorator = new HandDecorator($hand);
            $hash = $handDecorator->getCanonicalHandHash();

            if (!isset($memo[$hash])) {
                $tree = $this->getCanonicalProbabilityTree($hand);
                $node = $tree->getNodeWithHighestMeanPayout();
                $memo[$hash] = $node->getMeanPayout();
            }

            $sum += $memo[$hash];
        }

        $meanPayout = (float)($sum / 2598960);
        $this->persistMeanHighestPayoutToCache($meanPayout);
        return $meanPayout;
    }

    private function resolveMeanHighestPayoutFromCache(): ?float
    {
        try {
            $cached = $this->cache->get(self::MEAN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if (is_float($cached)) {
            return $cached;
        }

        return null;
    }

    private function persistMeanHighestPayoutToCache(float $meanPayout): void
    {
        try {
            $this->cache->set(self::MEAN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())), $meanPayout);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }
}
