<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\Game;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameException;
use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameNotFoundException;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepository;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Service\GameServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveCardsLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveHandDealtLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveSkill;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\PossibleDraws;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\InMemoryCache;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Serializer\SerializerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function count;
use function floor;
use function rand;
use function serialize;

class GameRepositoryTest extends TestCase
{
    /** @var GameInterface */
    private $game;

    /** @var GameRepositoryInterface */
    private $repository;

    /**
     * @throws GameException
     */
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
                    'Blah',
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

                $handDealtLuck = new MoveHandDealtLuck('Blah', $expectedPayout, $payout, $zScore);

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

        $this->game = new Game(GameId::generate(), $gameService);

        $this->game->cheat();
        for ($i = 0; $i < 10; $i++) {
            $this->game->bet();
            $this->game->play(Draw::fromId(rand(Draw::MIN_ID, Draw::MAX_ID)));
        }

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

        $this->repository = new GameRepository(
            $cache,
            $gameService
        );
    }

    /**
     * @throws GameNotFoundException
     */
    public function testGet(): void
    {
        $this->repository->save($this->game);
        $newGame = $this->repository->get($this->game->getId());

        $oldMeta = $this->game->getMeta();
        $meta = $newGame->getMeta();

        $oldState = $this->game->getState();
        $state = $newGame->getState();

        $this->assertEquals($oldMeta->getPurse(), $meta->getPurse());
        $this->assertEquals($oldMeta->getHighPurse(), $meta->getHighPurse());
        $this->assertEquals($oldMeta->getScore(), $meta->getScore());
        $this->assertEquals($oldMeta->getHighPurse(), $meta->getHighPurse());
        $this->assertEquals($oldMeta->getTurn(), $meta->getTurn());
        $this->assertEquals($oldMeta->getEfficiency(), $meta->getEfficiency());
        $this->assertEquals($oldMeta->getLuck(), $meta->getLuck());
        $this->assertEquals($oldMeta->getCheated(), $meta->getCheated());

        $this->assertEquals((string)$oldState->getHand(), (string)$state->getHand());
        $this->assertEquals(serialize($oldState->getCardsHeld()), serialize($state->getCardsHeld()));
        $this->assertEquals(serialize($oldState->getCardsDealt()), serialize($state->getCardsDealt()));
        $this->assertEquals((string)$oldState->getHandType(), (string)$state->getHandType());
    }

    /**
     * @throws GameNotFoundException
     */
    public function testGetOutOfBonds(): void
    {
        $id = GameId::generate();
        $this->expectException(GameNotFoundException::class);
        $this->repository->get($id);
    }

    public function testHas(): void
    {
        $this->repository->save($this->game);
        $this->assertTrue($this->repository->has($this->game->getId()));
        $this->assertFalse($this->repository->has(GameId::generate()));
    }

    public function testGetNew(): void
    {
        $game = $this->repository->getNew();
        $this->assertEquals(0, $game->getMeta()->getTurn());
    }

    public function testDelete(): void
    {
        $id = $this->game->getId();
        $this->repository->save($this->game);
        $this->assertTrue($this->repository->has($id));
        $this->repository->delete($this->game);
        $this->assertFalse($this->repository->has($id));
    }
}
