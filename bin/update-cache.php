<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Domain\Probability\Generator\CombinationGenerator;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\ProbabilityTreeCacheInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

require_once('../vendor/autoload.php');

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../app/container.php';

/** @var CacheInterface $cache */
$cache = $container->get(ProbabilityTreeCacheInterface::class);

if (!is_file('keys.php')) {
    /** @var RulesInterface $rules */
    $rules = $container->get(RulesInterface::class);

    $deck = new Deck();
    $generator = (new CombinationGenerator())($deck, Hand::HAND_SIZE);

    $keys = [];
    foreach ($generator as $cards) {
        $handDecorator = new HandDecorator(new Hand(...$cards));
        $toSerialize = ['hash' => $handDecorator->getCanonicalHandHash(), 'rules' => $rules->toArray()];
        $hash = md5(serialize($toSerialize));
        $keys[$hash] = $hash;
    }
    $keys = array_values($keys);
    sort($keys);
    file_put_contents('keys.txt', serialize($keys));
} else {
    $keys = unserialize(file_get_contents('keys.txt'));
}

try {
    foreach ($keys as $key) {
        /** @var ProbabilityTree $tree */
        $tree = $cache->get($key);
        $cache->set($key, $tree);
    }
} catch (InvalidArgumentException $e) {
    echo 'Caching error';
    die(1);
}
