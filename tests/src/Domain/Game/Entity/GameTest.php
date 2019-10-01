<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Game\Entity;

use Cliffordvickrey\TheGambler\Domain\Collection\CardCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\Game;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolver;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\PossibleDraws;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;
use function count;
use function get_class;
use function is_iterable;
use function rand;

class GameTest extends TestCase
{
    /** @var GameInterface */
    private $game;
    /** @var HandTypeResolver */
    private $handTypeResolver;
    /** @var ProbabilityServiceInterface */
    private $probabilityService;
    /** @var RulesInterface */
    private $rules;

    public function setUp(): void
    {
        $handTypeResolver = new HandTypeResolver();
        $this->handTypeResolver = $handTypeResolver;

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

        $this->rules = Rules::fromDefaults();

        $this->probabilityService = $probabilityService;

        $this->game = new Game(
            GameId::generate(),
            Rules::fromDefaults(),
            $this->handTypeResolver,
            $this->probabilityService
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
        $meta = $this->game->getMeta();

        for ($i = 0; $i < 10; $i++) {
            $this->game->bet();
            $drawId = rand(Draw::MIN_ID, Draw::MAX_ID);
            $draw = Draw::fromId($drawId);

            $moveAnalysis = $this->analyzeMove($state->getHand() ?? new Hand(), $draw);
            $expectedAmount = $moveAnalysis->getExpectedAmount();
            $maxExpectedAmount = $moveAnalysis->getMaxExpectedAmount();
            $meanMaxExpectedAmount = $moveAnalysis->getMeanMaxExpectedAmount();

            $oldPurse = $meta->getPurse();
            $oldTurn = $meta->getTurn();
            $oldLuck = $meta->getLuck();
            $oldEfficiency = $meta->getEfficiency();
            $turn = $meta->getTurn();
            $moveEfficiency = $expectedAmount / $maxExpectedAmount;
            $expectedEfficiency = (($oldEfficiency * $turn) + $moveEfficiency) / ($turn + 1);

            $this->game->play($draw);

            $hand = $state->getHand();
            $cardsHeld = $state->getCardsHeld();
            $cardsDealt = $state->getCardsDealt();
            $handType = $state->getHandType() ?? new HandType(HandType::NOTHING);
            $amount = $this->rules->getPayoutAmount($handType);
            $expectedPurse = $oldPurse + $amount;

            $moveLuck = (self::safeDivision($maxExpectedAmount, $meanMaxExpectedAmount) +
                    self::safeDivision($amount, $expectedAmount)) / 2;
            $expectedLuck = (($oldLuck * $oldTurn) + $moveLuck) / ($oldTurn + 1);

            $efficiency = $meta->getEfficiency();
            $this->assertEquals($expectedEfficiency, $efficiency);

            $luck = $meta->getLuck();
            $this->assertEquals($expectedLuck, $luck);

            $purse = $meta->getPurse();
            $this->assertEquals($expectedPurse, $purse);

            $this->assertInstanceOf(Hand::class, $hand);
            $this->assertInstanceOf(CardCollection::class, $cardsHeld);
            $this->assertInstanceOf(CardCollection::class, $cardsDealt);
            $this->assertInstanceOf(HandType::class, $handType);

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
            $newHandType = $this->handTypeResolver->resolve($newHand);
            $this->assertEquals((string)$newHandType, (string)$handType);
        }
    }

    private function analyzeMove(Hand $hand, Draw $draw): MoveAnalysis
    {
        $tree = $this->probabilityService->getProbabilityTree($hand);
        $node = $tree->getNode($draw);
        $highestNode = $tree->getNodeWithHighestMeanPayout();

        $expectedAmount = $node->getMeanPayout();
        $maxExpectedAmount = $highestNode->getMeanPayout();
        $meanMaxExpectedAmount = $this->probabilityService->getMeanHighestPayout();

        return new MoveAnalysis($expectedAmount, $maxExpectedAmount, $meanMaxExpectedAmount);
    }

    /**
     * @param float|int $dividend
     * @param float|int $divisor
     * @return float
     */
    private static function safeDivision($dividend, $divisor): float
    {
        if (0 === $divisor || 0.0 === $divisor) {
            return 1.0;
        }

        return (float)($dividend / $divisor);
    }
}
