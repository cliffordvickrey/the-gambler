<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Serializer;

use Cliffordvickrey\TheGambler\Domain\Collection\CardCollection;
use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Game\Collection\HighScores;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameMeta;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameState;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\HighScore;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveAnalysis;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveCardsLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveHandDealtLuck;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\MoveSkill;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityNode;
use Cliffordvickrey\TheGambler\Domain\Probability\ValueObject\ProbabilityTree;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\PhpSerializer;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\SerializerInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Session\Session;
use Psr\Container\ContainerInterface;

class SerializerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): SerializerInterface
    {
        return new PhpSerializer([
            Card::class,
            CardCollection::class,
            Draw::class,
            Hand::class,
            HandType::class,
            HighScore::class,
            HighScores::class,
            GameId::class,
            GameMeta::class,
            GameState::class,
            MoveAnalysis::class,
            MoveSkill::class,
            MoveCardsLuck::class,
            MoveHandDealtLuck::class,
            ProbabilityNode::class,
            ProbabilityTree::class,
            Session::class
        ]);
    }
}
