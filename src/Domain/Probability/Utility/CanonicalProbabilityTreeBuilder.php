<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\Utility;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Generator\CombinationGenerator;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Domain\ValueObject\PossibleDraws;
use function array_combine;
use function array_fill;
use function array_map;
use function count;
use function md5;
use function range;
use function serialize;
use function str_split;

class CanonicalProbabilityTreeBuilder
{
    private $handTypeResolver;
    private $rules;
    private $possibleDraws;

    private $combinationMemo = [];
    private $handTypeByHandIdMemo = [];
    private $handTypeByHandTypeHashMemo = [];

    public function __construct(HandTypeResolverInterface $handTypeResolver, RulesInterface $rules)
    {
        $this->handTypeResolver = $handTypeResolver;
        $this->rules = $rules;
        $this->possibleDraws = new PossibleDraws();
    }

    public function buildCanonicalProbabilityTree(Hand $hand): ProbabilityTree
    {
        if (0 === count($this->handTypeByHandTypeHashMemo)) {
            $this->warmHandTypeCache();
        }

        $handDecorator = new HandDecorator($hand);
        $hand = $handDecorator->getHandWithCanonicalSort();

        $deck = new Deck();
        foreach ($hand as $card) {
            $deck->slice($card);
        }
        $remainingCardIds = [];
        foreach ($deck as $card) {
            $remainingCardIds[] = (string)$card;
        }

        $carry = ['hashes' => $handDecorator->getCardHashes()];
        $nodes = [];
        foreach ($this->possibleDraws as $draw) {
            $nodes[] = $this->getProbabilityNode($remainingCardIds, $hand, $draw, $carry);
        }

        return new ProbabilityTree(...$nodes);
    }

    private function warmHandTypeCache(): void
    {
        $deck = new Deck();
        $generator = (new CombinationGenerator())($deck, Hand::HAND_SIZE);
        foreach ($generator as $hand) {
            $hand = new Hand(...$hand);
            $decorator = new HandDecorator($hand);
            $hash = $decorator->getCanonicalHandTypeHash();
            if (!isset($this->handTypeByHandTypeHashMemo[$hash])) {
                $this->handTypeByHandTypeHashMemo[$hash] = (string)$this->handTypeResolver->resolve($hand);
            }
        }
    }

    private function getProbabilityNode(
        array $remainingCardIds,
        Hand $hand,
        Draw $draw,
        array &$carry
    ): ProbabilityNode
    {
        $enum = HandType::getEnum();

        list ($cardIdsHeld, $cardsHeldHash) = $this->getCardIdsHeldAndCardsHeldHash($hand, $draw, $carry['hashes']);
        $cached = $carry[$cardsHeldHash] ?? null;
        if (null !== $cached) {
            list ($frequencies, $meanPayout) = $cached;
            return new ProbabilityNode($draw, $frequencies, $this->rules, $meanPayout);
        }

        $frequencies = array_combine($enum, array_fill(0, count($enum), 0)) ?: [];
        $handTypes = $this->getHandTypes($remainingCardIds, $cardIdsHeld);

        foreach ($handTypes as $handType) {
            $frequencies[$handType] += 1;
        }

        $node = new ProbabilityNode($draw, $frequencies, $this->rules);
        $carry[$cardsHeldHash] = [$frequencies, $node->getMeanPayout()];

        return $node;
    }

    private function getCardIdsHeldAndCardsHeldHash(Hand $hand, Draw $draw, array $hashes): array
    {
        $cardIdsHeld = [];
        $cardHashesHeld = [];
        foreach ($draw as $i => $hold) {
            if ($hold) {
                $cardIdsHeld[] = (string)$hand->getByOffset((int)$i);
                $cardHashesHeld[] = $hashes[$i];
            }
        }

        $cardsHeldHash = md5(serialize($cardHashesHeld));
        return [$cardIdsHeld, $cardsHeldHash];
    }

    /**
     * @param array $remainingCardIds
     * @param array $cardIdsHeld
     * @return array
     */
    private function getHandTypes(array $remainingCardIds, array $cardIdsHeld): array
    {
        $heldCount = count($cardIdsHeld);

        if (Hand::HAND_SIZE === $heldCount) {
            $handId = $cardIdsHeld[0] . $cardIdsHeld[1] . $cardIdsHeld[2] . $cardIdsHeld[3] . $cardIdsHeld[4];

            if (isset($this->handTypeByHandIdMemo[$handId])) {
                $handType = $this->handTypeByHandIdMemo[$handId];
            } else {
                $handType = $this->getHandType($handId);
            }

            return [$handType];
        }

        $generator = $this->getCombinationGenerator(Hand::HAND_SIZE - $heldCount);
        $handTypes = [];

        switch ($heldCount) {
            case 0:
                foreach ($generator as $indexes) {
                    $handId = $remainingCardIds[$indexes[0]] .
                        $remainingCardIds[$indexes[1]] .
                        $remainingCardIds[$indexes[2]] .
                        $remainingCardIds[$indexes[3]] .
                        $remainingCardIds[$indexes[4]];

                    if (isset($this->handTypeByHandIdMemo[$handId])) {
                        $handType = $this->handTypeByHandIdMemo[$handId];
                    } else {
                        $handType = $this->getHandType($handId);
                    }

                    $handTypes[] = $handType;
                }
                break;
            case 1:
                foreach ($generator as $indexes) {
                    $handId = $cardIdsHeld[0] .
                        $remainingCardIds[$indexes[0]] .
                        $remainingCardIds[$indexes[1]] .
                        $remainingCardIds[$indexes[2]] .
                        $remainingCardIds[$indexes[3]];

                    if (isset($this->handTypeByHandIdMemo[$handId])) {
                        $handType = $this->handTypeByHandIdMemo[$handId];
                    } else {
                        $handType = $this->getHandType($handId);
                    }

                    $handTypes[] = $handType;
                }
                break;
            case 2:
                foreach ($generator as $indexes) {
                    $handId = $cardIdsHeld[0] .
                        $cardIdsHeld[1] .
                        $remainingCardIds[$indexes[0]] .
                        $remainingCardIds[$indexes[1]] .
                        $remainingCardIds[$indexes[2]];

                    if (isset($this->handTypeByHandIdMemo[$handId])) {
                        $handType = $this->handTypeByHandIdMemo[$handId];
                    } else {
                        $handType = $this->getHandType($handId);
                    }

                    $handTypes[] = $handType;
                }
                break;
            case 3:
                foreach ($generator as $indexes) {
                    $handId = $cardIdsHeld[0] .
                        $cardIdsHeld[1] .
                        $cardIdsHeld[2] .
                        $remainingCardIds[$indexes[0]] .
                        $remainingCardIds[$indexes[1]];

                    if (isset($this->handTypeByHandIdMemo[$handId])) {
                        $handType = $this->handTypeByHandIdMemo[$handId];
                    } else {
                        $handType = $this->getHandType($handId);
                    }

                    $handTypes[] = $handType;
                }
                break;
            default:
                foreach ($generator as $indexes) {
                    $handId = $cardIdsHeld[0] .
                        $cardIdsHeld[1] .
                        $cardIdsHeld[2] .
                        $cardIdsHeld[3] .
                        $remainingCardIds[$indexes[0]];

                    if (isset($this->handTypeByHandIdMemo[$handId])) {
                        $handType = $this->handTypeByHandIdMemo[$handId];
                    } else {
                        $handType = $this->getHandType($handId);
                    }

                    $handTypes[] = $handType;
                }

                break;
        }

        return $handTypes;
    }

    private function getHandType(string $handId): string
    {
        $cardIds = str_split($handId, 2);
        $cards = array_map(function (string $cardId): Card {
            return Card::fromId((int)$cardId);
        }, $cardIds);
        $hand = new Hand(...$cards);
        $handDecorator = new HandDecorator($hand);
        $handTypeHash = $handDecorator->getCanonicalHandTypeHash();
        $handType = $this->handTypeByHandTypeHashMemo[$handTypeHash];
        $this->handTypeByHandIdMemo[$handId] = $handType;
        return $handType;
    }

    /**
     * @param int $numberChosen
     * @return array[]
     */
    private function getCombinationGenerator(int $numberChosen): array
    {
        if (isset($this->combinationMemo[$numberChosen])) {
            return $this->combinationMemo[$numberChosen];
        }

        $indexes = range(0, Card::MAX_ID - 6);
        $generator = (new CombinationGenerator())($indexes, $numberChosen);
        $combinations = [];
        foreach ($generator as $combination) {
            $combinations[] = $combination;
        }
        $this->combinationMemo[$numberChosen] = $combinations;
        return $combinations;
    }
}
