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
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
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
use function min;
use function range;
use function serialize;
use function sprintf;
use const PHP_SAPI;

class ProbabilityService implements ProbabilityServiceInterface
{
    const LOG_MEAN_PAYOUT_CACHE_KEY = 'logMeanPayout';
    const LOG_ST_DEV_PAYOUT_CACHE_KEY = 'logStDev';
    const MEAN_PAYOUT_CACHE_KEY = 'meanPayout';
    const MIN_PAYOUT_CACHE_KEY = 'minPayout';
    const ST_DEV_PAYOUT_CACHE_KEY = 'stDev';

    private $cache;
    private $handTypeResolver;
    private $rules;
    private $treeBuilder;
    private $possibleDraws;
    private $payouts;

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

    public function getStandardDeviationOfHighestPayout(): float
    {
        $cached = $this->resolveStDevHighestPayoutFromCache();
        if (is_float($cached)) {
            return $cached;
        }

        $payouts = $this->getPayouts();
        $stDev = Math::stDev($payouts);
        $this->persistStDevHighestPayoutToCache($stDev);
        return $stDev;
    }

    public function getLogStandardDeviationOfHighestPayout(): float
    {
        $cached = $this->resolveLogStDevHighestPayoutFromCache();
        if (is_float($cached)) {
            return $cached;
        }

        $payouts = $this->getPayouts();
        $stDev = Math::stDev(Math::logTransform($payouts));
        $this->persistLogStDevHighestPayoutToCache($stDev);
        return $stDev;
    }

    /**
     * @return float[]
     */
    private function getPayouts(): array
    {
        if (null !== $this->payouts) {
            return $this->payouts;
        }


        self::assertCli();
        $deck = new Deck();
        $generator = (new CombinationGenerator())($deck, Hand::HAND_SIZE);

        $memo = [];
        $payouts = [];
        foreach ($generator as $cards) {
            $hand = new Hand(...$cards);
            $handDecorator = new HandDecorator($hand);
            $hash = $handDecorator->getCanonicalHandHash();

            if (!isset($memo[$hash])) {
                $tree = $this->getCanonicalProbabilityTree($hand);
                $node = $tree->getNodeWithHighestMeanPayout();
                $memo[$hash] = $node->getMeanPayout();
            }

            $payouts[] = $memo[$hash];
        }

        $this->payouts = $payouts;
        return $payouts;
    }

    private function resolveStDevHighestPayoutFromCache(): ?float
    {
        try {
            $cached = $this->cache->get(self::ST_DEV_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if (is_float($cached)) {
            return $cached;
        }

        return null;
    }

    public function getMeanHighestPayout(): float
    {
        $cached = $this->resolveMeanHighestPayoutFromCache();
        if (is_float($cached)) {
            return $cached;
        }

        self::assertCli();

        $payouts = $this->getPayouts();
        $mean = Math::mean($payouts);
        $this->persistMeanHighestPayoutToCache($mean);
        return $mean;
    }


    public function getMinHighestPayout(): float
    {
        $cached = $this->resolveMinHighestPayoutFromCache();
        if (is_float($cached)) {
            return $cached;
        }

        self::assertCli();

        $payouts = $this->getPayouts();
        $mean = min($payouts);
        $this->persistMinHighestPayoutToCache($mean);
        return $mean;
    }

    public function getLogMeanHighestPayout(): float
    {
        $cached = $this->resolveLogMeanHighestPayoutFromCache();
        if (is_float($cached)) {
            return $cached;
        }

        self::assertCli();

        $payouts = $this->getPayouts();
        $mean = Math::mean(Math::logTransform($payouts));
        $this->persistLogMeanHighestPayoutToCache($mean);
        return $mean;
    }

    private function persistMeanHighestPayoutToCache(float $meanPayout): void
    {
        try {
            $this->cache->set(self::MEAN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())), $meanPayout);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
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

    private function persistMinHighestPayoutToCache(float $minPayout): void
    {
        try {
            $this->cache->set(self::MIN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())), $minPayout);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }

    private function resolveMinHighestPayoutFromCache(): ?float
    {
        try {
            $cached = $this->cache->get(self::MIN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if (is_float($cached)) {
            return $cached;
        }

        return null;
    }


    private function persistStDevHighestPayoutToCache(float $stDevPayout): void
    {
        try {
            $this->cache->set(
                self::ST_DEV_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())), $stDevPayout
            );
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }

    private function persistLogMeanHighestPayoutToCache(float $logMeanPayout): void
    {
        try {
            $this->cache->set(self::LOG_MEAN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())), $logMeanPayout);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }

    private function resolveLogMeanHighestPayoutFromCache(): ?float
    {
        try {
            $cached = $this->cache->get(self::LOG_MEAN_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if (is_float($cached)) {
            return $cached;
        }

        return null;
    }

    private function persistLogStDevHighestPayoutToCache(float $logStDevPayout): void
    {
        try {
            $this->cache->set(self::LOG_ST_DEV_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())), $logStDevPayout);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }
    }

    private function resolveLogStDevHighestPayoutFromCache(): ?float
    {
        try {
            $cached = $this->cache->get(self::LOG_ST_DEV_PAYOUT_CACHE_KEY . md5(serialize($this->rules->toArray())));
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException(sprintf('There was a caching error: %s', $e->getMessage()));
        }

        if (is_float($cached)) {
            return $cached;
        }

        return null;
    }
}
