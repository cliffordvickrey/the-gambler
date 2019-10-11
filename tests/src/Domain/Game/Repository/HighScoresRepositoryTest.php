<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepository;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoreRepository;
use Cliffordvickrey\TheGambler\Domain\Game\Service\GameServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveCardsLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveHandDealtLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveSkill;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\InMemoryCache;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Serializer\SerializerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function array_multisort;
use function floor;
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
        $gameService = new class implements GameServiceInterface
        {
            private $handTypeResolver;
            private $rules;

            public function __construct()
            {
                $this->handTypeResolver = new HandTypeResolver();
                $this->rules = Rules::fromDefaults();
            }

            public function getStartingPurse(): int
            {
                return $this->rules->getStartingPurse();
            }

            public function getDefaultBetAmount(): int
            {
                return $this->rules->getBetAmount();
            }

            public function analyzeMove(GameState $state, Draw $draw, int $betAmount): MoveAnalysis
            {
                $handType = $state->getHandType();
                $payoutRatio = (float)($betAmount / $this->rules->getBetAmount());
                $payout = (int)floor($this->rules->getPayoutAmount($handType) * $payoutRatio);

                $expectedPayout = (float)($payoutRatio * rand(1, 5000));
                $optimalExpectedPayout = (float)($payoutRatio * rand(1, 5000));

                $skill = new MoveSkill($expectedPayout, Draw::fromId(1), $optimalExpectedPayout);

                $minExpected = 0.0;

                $logOptimalExpected = Math::logTransformScalar($optimalExpectedPayout, $minExpected);

                $logMeanOptimalExpected = rand(1, 10);
                $logOptimalStDev = rand(1, 10);

                $cardsLuck = new MoveCardsLuck(
                    $optimalExpectedPayout,
                    Math::standardize($logOptimalExpected, $logMeanOptimalExpected, $logOptimalStDev)
                );

                $logPayout = Math::logTransformScalar($payout, 0.0);

                $logStDev = rand(1, 10);
                $zScore = null;
                if (0.0 !== $logStDev) {
                    $zScore = Math::standardize(
                        $logPayout,
                        rand(1, 10),
                        $logStDev
                    );
                }

                $handDealtLuck = new MoveHandDealtLuck($expectedPayout, $payout, $zScore);

                return new MoveAnalysis($skill, $cardsLuck, $handDealtLuck);
            }

            public function getProbabilityTree(Hand $hand): ProbabilityTree
            {
                return new ProbabilityTree();
            }

            public function resolve(Hand $hand): HandType
            {
                return $this->handTypeResolver->resolve($hand);
            }
        };

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

        $this->gameRepository = new GameRepository($cache, $gameService);

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

        for ($i = 0; $i < 3; $i++) {
            foreach ($games as $ii => $game) {
                $meta = $game->getMeta();
                $meta->addToPurse(rand(0, 100000), (float)(rand(0, 100) / 100), (float)(rand(0, 1) / 100));

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