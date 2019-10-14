<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Game\Entity;

use Cliffordvickrey\TheGambler\Domain\Collection\CardCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\Game;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Service\GameServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveCardsLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveHandDealtLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveSkill;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;
use function floor;
use function is_iterable;
use function rand;

class GameTest extends TestCase
{
    /** @var GameInterface */
    private $game;
    /** @var GameServiceInterface */
    private $gameService;

    public function setUp(): void
    {
        $this->gameService = new class implements GameServiceInterface
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

        $this->game = new Game(
            GameId::generate(),
            $this->gameService
        );
    }

    public function testGetId(): void
    {
        $id = $this->game->getId();
        $this->assertInstanceOf(GameId::class, $id);
    }

    public function testCheat(): void
    {
        $meta = $this->game->getMeta();
        $this->assertEquals(false, $meta->getCheated());
        $this->game->cheat();
        $this->assertEquals(true, $meta->getCheated());
    }

    public function testBet(): void
    {
        $this->game->bet();

        $state = $this->game->getState();
        $hand = $state->getHand();
        $cardsHeld = $state->getCardsHeld();
        $cardsDealt = $state->getCardsDealt();
        $handType = $state->getHandType();

        $meta = $this->game->getMeta();
        $purse = $meta->getPurse();

        $this->assertInstanceOf(Hand::class, $hand);
        $this->assertNull($cardsHeld);
        $this->assertNull($cardsDealt);
        $this->assertNull($handType);

        $this->assertEquals(Rules::DEFAULT_STARTING_PURSE - Rules::DEFAULT_BET_AMOUNT, $purse);
    }

    public function testBetAlreadyBet(): void
    {
        $this->game->bet();
        $this->expectExceptionMessage('Cannot deal cards; hand has not been played');
        $this->game->bet();
    }

    public function testBetNotEnoughFunds(): void
    {
        $meta = $this->game->getMeta();
        $meta->bet(Rules::DEFAULT_STARTING_PURSE);
        $this->expectExceptionMessage('Cannot deal cards; not enough funds for bet');
        $this->game->bet();
    }

    public function testBetNotEnoughFundsCheated(): void
    {
        $this->game->cheat();
        $meta = $this->game->getMeta();
        $meta->bet(Rules::DEFAULT_STARTING_PURSE);
        $this->game->bet();
        $this->assertInstanceOf(Hand::class, $this->game->getState()->getHand());
    }

    public function testSplice(): void
    {
        $state = $this->game->getState();
        $this->game->cheat();
        $this->game->bet();

        if (null === $state->getHand()) {
            throw new UnexpectedValueException('Expected instance of ' . Hand::class);
        }

        $hand = clone $state->getHand();
        $deck = new Deck();
        foreach ($hand as $card) {
            $deck->slice($card);
        }
        $newCard = $deck->sliceRandom();
        $offset = rand(0, Hand::HAND_SIZE - 1);
        $hand->splice($offset, $newCard);
        $this->game->spliceHand($offset, $newCard);
        $this->assertEquals((string)$hand, (string)$state->getHand());
    }

    public function testSpliceInvalidState(): void
    {
        $state = $this->game->getState();
        $this->game->cheat();
        $this->game->bet();
        $this->game->play(Draw::fromId(1));

        if (null === $state->getHand()) {
            throw new UnexpectedValueException('Expected instance of ' . Hand::class);
        }

        $hand = clone $state->getHand();
        $this->expectExceptionMessage('Cannot alter hand (cheater!); hand has already been played');
        $this->game->spliceHand(0, $hand->getByOffset(0));
    }

    public function testSpliceWithoutCheating(): void
    {
        $state = $this->game->getState();
        $this->game->bet();

        if (null === $state->getHand()) {
            throw new UnexpectedValueException('Expected instance of ' . Hand::class);
        }

        $hand = clone $state->getHand();
        $deck = new Deck();
        foreach ($hand as $card) {
            $deck->slice($card);
        }
        $newCard = $deck->sliceRandom();
        $offset = rand(0, Hand::HAND_SIZE - 1);
        $hand->splice($offset, $newCard);
        $this->expectExceptionMessage('Cannot modify the hand; player must cheat first');
        $this->game->spliceHand($offset, $newCard);
    }

    public function testPlay(): void
    {
        $state = $this->game->getState();

        for ($i = 0; $i < 10; $i++) {
            $this->game->bet();
            $drawId = rand(Draw::MIN_ID, Draw::MAX_ID);
            $draw = Draw::fromId($drawId);

            $analysis = $this->game->getAnalysis();
            $this->assertNull($analysis);

            $this->game->play($draw);

            $hand = $state->getHand();
            $cardsHeld = $state->getCardsHeld();
            $cardsDealt = $state->getCardsDealt();
            $handType = $state->getHandType();
            $analysis = $this->game->getAnalysis();

            $this->assertInstanceOf(Hand::class, $hand);
            $this->assertInstanceOf(CardCollection::class, $cardsHeld);
            $this->assertInstanceOf(CardCollection::class, $cardsDealt);
            $this->assertInstanceOf(HandType::class, $handType);
            $this->assertInstanceOf(MoveAnalysis::class, $analysis);

            if (!is_iterable($cardsDealt) || !is_iterable($cardsHeld)) {
                throw new RuntimeException('Expected iterable collection');
            }

            $newHand = new Hand();
            foreach ($cardsDealt as $card) {
                $newHand->push($card);
            }
            foreach ($cardsHeld as $card) {
                $newHand->push($card);
            }

            $this->assertTrue($newHand->isValid());
            $newHandType = $this->gameService->resolve($newHand);
            $this->assertEquals((string)$newHandType, (string)$handType);
        }
    }
}
