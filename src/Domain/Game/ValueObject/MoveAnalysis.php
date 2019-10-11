<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use UnexpectedValueException;
use function serialize;
use function sprintf;
use function unserialize;

final class MoveAnalysis implements PortableInterface
{
    private $skill;
    private $cardsLuck;
    private $handDealtLuck;

    public function __construct(
        MoveSkill $skill,
        MoveCardsLuck $cardsLuck,
        MoveHandDealtLuck $handDealtLuck
    )
    {
        $this->skill = $skill;
        $this->cardsLuck = $cardsLuck;
        $this->handDealtLuck = $handDealtLuck;
    }

    public function __clone()
    {
        $this->skill = clone $this->skill;
        $this->cardsLuck = clone $this->cardsLuck;
        $this->handDealtLuck = clone $this->handDealtLuck;
    }

    /**
     * @return MoveSkill
     */
    public function getSkill(): MoveSkill
    {
        return $this->skill;
    }

    /**
     * @return MoveCardsLuck
     */
    public function getCardsLuck(): MoveCardsLuck
    {
        return $this->cardsLuck;
    }

    /**
     * @return MoveHandDealtLuck
     */
    public function getHandDealtLuck(): MoveHandDealtLuck
    {
        return $this->handDealtLuck;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    private function toArray(): array
    {
        return [
            'skill' => $this->skill,
            'cardsLuck' => $this->cardsLuck,
            'handDealtLuck' => $this->handDealtLuck
        ];
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => [
            Draw::class,
            MoveSkill::class,
            MoveCardsLuck::class,
            MoveHandDealtLuck::class
        ]]);

        $skill = $unSerialized['skill'] ?? null;
        if (!($skill instanceof MoveSkill)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', MoveSkill::class));
        }

        $cardsLuck = $unSerialized['cardsLuck'] ?? null;
        if (!($cardsLuck instanceof MoveCardsLuck)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', MoveCardsLuck::class));
        }

        $handDealtLuck = $unSerialized['handDealtLuck'] ?? null;
        if (!($handDealtLuck instanceof MoveHandDealtLuck)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', MoveHandDealtLuck::class));
        }

        $this->skill = $skill;
        $this->cardsLuck = $cardsLuck;
        $this->handDealtLuck = $handDealtLuck;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize($this->toArray());
    }
}

