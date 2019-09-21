<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepository;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoreRepository;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\PossibleDraws;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\InMemoryCache;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Serializer\SerializerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function array_multisort;
use function count;
use function rand;
use const SORT_DESC;
use const SORT_NUMERIC;

class HighScoresRepositoryTest extends TestCase
{
    /** @var GameRepositoryInterface */
    private $gameRepository;
    /** @var HighScoreRepository */
    private $highScoreRepository;

    public function setUp(): void
    {
        $probabilityService = new class implements ProbabilityServiceInterface
        {
            /** @var ProbabilityTree[] */
            private $memo = [];
            private $rules;

            public function __construct()
            {
                $this->rules = Rules::fromDefaults();
            }

            public function getCanonicalProbabilityTree(Hand $hand): ProbabilityTree
            {
                return new ProbabilityTree();
            }

            public function getProbabilityTree(Hand $hand): ProbabilityTree
            {
                $decorator = new HandDecorator($hand);
                $hash = $decorator->getCanonicalHandTypeHash();
                if (isset($this->memo[$hash])) {
                    return $this->memo[$hash];
                }

                $draws = new PossibleDraws();
                $handTypes = HandType::getEnum();
                $nodes = [];
                foreach ($draws as $draw) {
                    $frequencies = [];
                    foreach ($handTypes as $handType) {
                        $frequencies[(string)$handType] = rand(0, 1000);
                    }
                    $nodes[] = new ProbabilityNode($draw, $frequencies, $this->rules);
                }

                $this->memo[$hash] = new ProbabilityTree(...$nodes);
                return $this->memo[$hash];
            }

            public function getMeanHighestPayout(): float
            {
                $sum = 0.0;
                foreach ($this->memo as $tree) {
                    $sum += $tree->getNodeWithHighestMeanPayout()->getMeanPayout();
                }
                return $sum / count($this->memo);
            }
        };

        $handTypeResolver = new HandTypeResolver();
        $rules = Rules::fromDefaults();

        $cache = new class extends InMemoryCache
        {
            private $serializer;

            public function __construct()
            {
                $mockContainer = new class implements ContainerInterface
                {
                    public function get($id)
                    {
                        return [];
                    }

                    public function has($id)
                    {
                        return false;
                    }
                };

                $factory = new SerializerFactory();
                $serializer = $factory($mockContainer);
                $this->serializer = $serializer;
            }

            public function get($key, $default = null)
            {
                if ($this->has($key)) {
                    return $this->serializer->unSerialize((string)parent::get($key));
                }
                return $default;
            }

            public function set($key, $value, $ttl = null)
            {
                $value = $this->serializer->serialize($value);
                return parent::set($key, $value, $ttl);
            }
        };

        $this->gameRepository = new GameRepository(
            $cache,
            $handTypeResolver,
            $probabilityService,
            $rules
        );

        $this->highScoreRepository = new HighScoreRepository($cache, 10);
    }

    public function testAdd()
    {
        $player = new Player('CDV');

        /** @var GameInterface[] $games */
        $games = [];
        $scores = [];

        for ($i = 0; $i < 100; $i++) {
            $game = $this->gameRepository->getNew();
            $games[$i] = $game;
        }

        $analysis = new MoveAnalysis(
            (float)rand(0, 100),
            (float)rand(0, 100),
            (float)rand(0, 100)
        );

        for ($i = 0; $i < 3; $i++) {
            foreach ($games as $ii => $game) {
                $meta = $game->getMeta();
                $meta->addToPurse(rand(0, 100000), $analysis);

                $scores[$ii] = $meta->getScore();

                $this->highScoreRepository->add($player, $game);
            }
        }

        $highScores = $this->highScoreRepository->get();
        $this->assertCount(10, $highScores);

        array_multisort($scores, SORT_DESC, SORT_NUMERIC, $games);

        foreach ($games as $i => $game) {
            $meta = $game->getMeta();
            $score = $meta->getScore();
            $rank = $highScores->getRank($score + 1);

            if ($i > 10) {
                $expectedRank = 11;
            } else {
                $expectedRank = $i + 1;
            }
            $this->assertEquals($expectedRank, $rank);
        }
    }
}