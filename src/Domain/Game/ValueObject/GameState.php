<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Collection\CardCollection;
use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Exception\GameException;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use function array_merge;
use function count;
use function is_int;
use function serialize;
use function unserialize;

final class GameState implements PortableInterface
{
    private $hand;
    private $betAmount;
    private $cardsHeld;
    private $cardsDealt;
    private $handType;

    public function __construct(
        ?Hand $hand = null,
        ?int $betAmount = null,
        ?CardCollection $cardsHeld = null,
        ?CardCollection $cardsDealt = null,
        ?HandType $handType = null
    )
    {
        $this->hand = $hand;
        $this->betAmount = $betAmount;
        $this->cardsHeld = $cardsHeld;
        $this->cardsDealt = $cardsDealt;
        $this->handType = $handType;
    }

    public function __clone()
    {
        if (null !== $this->hand) {
            $this->hand = clone $this->hand;
        }

        if (null !== $this->cardsHeld) {
            $this->cardsHeld = clone $this->cardsHeld;
        }

        if (null !== $this->cardsDealt) {
            $this->cardsDealt = clone $this->cardsDealt;
        }

        if (null !== $this->handType) {
            $this->handType = clone $this->handType;
        }
    }

    /**
     * @return Hand|null
     */
    public function getHand(): ?Hand
    {
        return $this->hand;
    }

    /**
     * @return CardCollection|null
     */
    public function getCardsHeld(): ?CardCollection
    {
        return $this->cardsHeld;
    }

    /**
     * @return HandType|null
     */
    public function getHandType(): ?HandType
    {
        return $this->handType;
    }

    public function jsonSerialize(): array
    {
        return [
            'hand' => $this->hand,
            'betAmount' => Format::dollarFormat($this->betAmount),
            'cardsHeld' => $this->cardsHeld,
            'cardsDealt' => $this->cardsDealt,
            'handType' => $this->handType
        ];
    }

    public function toArray(): array
    {
        return [
            'hand' => $this->hand,
            'betAmount' => $this->betAmount,
            'cardsHeld' => $this->cardsHeld,
            'cardsDealt' => $this->cardsDealt,
            'handType' => $this->handType
        ];
    }

    /**
     * @return CardCollection|null
     */
    public function getCardsDealt(): ?CardCollection
    {
        return $this->cardsDealt;
    }

    /**
     * @param int $betAmount
     * @throws GameException
     */
    public function deal(int $betAmount): void
    {
        if (null !== $this->hand && null === $this->handType) {
            throw new GameException('Cannot deal cards; hand has not been played');
        }

        $this->clearState();
        $cards = $this->drawCards();
        $this->hand = new Hand(...$cards);
        $this->betAmount = $betAmount;
    }

    private function clearState(): void
    {
        $this->hand = null;
        $this->betAmount = null;
        $this->cardsHeld = null;
        $this->cardsDealt = null;
        $this->handType = null;
    }

    /**
     * @return Card[]
     * @throws GameException
     */
    private function drawCards(): array
    {
        $amount = Hand::HAND_SIZE;
        if (null !== $this->cardsHeld) {
            $amount = Hand::HAND_SIZE - count($this->cardsHeld);
        }

        if (0 === $amount) {
            return [];
        }

        $deck = $this->getDeck();

        if ($amount > count($deck)) {
            throw new GameException('Not enough cards in the deck to draw');
        }

        $cardsDrawn = [];

        for ($i = 0; $i < $amount; $i++) {
            $cardsDrawn[] = $deck->sliceRandom();
        }

        return $cardsDrawn;
    }

    private function getDeck(): Deck
    {
        $deck = new Deck();
        if (null === $this->hand) {
            return $deck;
        }

        foreach ($this->hand as $card) {
            $deck->slice($card);
        }
        return $deck;
    }

    /**
     * @param Draw $draw
     * @param HandTypeResolverInterface $handTypeResolver
     * @throws GameException
     */
    public function play(Draw $draw, HandTypeResolverInterface $handTypeResolver): void
    {
        if (null !== $this->handType) {
            throw new GameException('Cannot play hand; hand has already been played');
        }

        $cardsHeld = $this->getHeldCards($draw);
        $this->cardsHeld = new CardCollection(...$cardsHeld);

        $cardsDealt = $this->drawCards();
        $this->cardsDealt = new CardCollection(...$cardsDealt);

        $finalCards = array_merge($cardsHeld, $cardsDealt);
        $finalHand = new Hand(...$finalCards);
        $this->handType = $handTypeResolver->resolve($finalHand);
    }

    /**
     * @param Draw $draw
     * @return Card[]
     */
    private function getHeldCards(Draw $draw): array
    {
        if (null === $this->hand) {
            return [];
        }

        $heldCards = [];
        foreach ($draw as $i => $hold) {
            if (!$hold) {
                continue;
            }
            $heldCards[] = $this->hand->getByOffset((int)$i);
        }
        return $heldCards;
    }

    /**
     * @param int $offset
     * @param Card $card
     * @throws GameException
     */
    public function splice(int $offset, Card $card)
    {
        if (null !== $this->handType) {
            throw new GameException('Cannot alter hand (cheater!); hand has already been played');
        }

        if (null === $this->hand) {
            throw new GameException('Cannot alter hand (cheater!); player has not yet bet');
        }

        $hand = clone $this->hand;
        $hand->splice($offset, $card);

        if (!$hand->isValid()) {
            throw new GameException('Cannot alter hand; resulting hand is invalid');
        }

        $this->hand = $hand;
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize(
            $serialized,
            ['allowed_classes' => [CardCollection::class, Card::class, Hand::class, HandType::class]]
        );
        $hand = $unSerialized['hand'] ?? null;
        $betAmount = $unSerialized['betAmount'] ?? null;
        $cardsHeld = $unSerialized['cardsHeld'] ?? null;
        $cardsDealt = $unSerialized['cardsDealt'] ?? null;
        $handType = $unSerialized['handType'] ?? null;

        if (!($hand instanceof Hand)) {
            $hand = null;
        }

        if (!is_int($betAmount)) {
            $betAmount = null;
        }

        if (!($cardsHeld instanceof CardCollection)) {
            $cardsHeld = null;
        }

        if (!($cardsDealt instanceof CardCollection)) {
            $cardsDealt = null;
        }

        if (!($handType instanceof HandType)) {
            $handType = null;
        }

        $static = new static($hand, $betAmount, $cardsHeld, $cardsDealt, $handType);
        $this->hand = $static->hand;
        $this->betAmount = $static->betAmount;
        $this->cardsHeld = $static->cardsHeld;
        $this->cardsDealt = $static->cardsDealt;
        $this->handType = $static->handType;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * @return int|null
     */
    public function getBetAmount(): ?int
    {
        return $this->betAmount;
    }
}
