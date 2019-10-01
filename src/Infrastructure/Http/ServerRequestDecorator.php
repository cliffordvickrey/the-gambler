<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Http;

use Cliffordvickrey\TheGambler\Api\Middleware\AuthenticationMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\BetMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\GameResolvingMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\PlayMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SessionMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SpliceHandMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Card;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Cliffordvickrey\TheGambler\Infrastructure\Session\Session;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use function is_scalar;
use function trim;

class ServerRequestDecorator
{
    private $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getSession(): Session
    {
        $session = $this->getSessionNullable();
        if (null === $session) {
            throw new RuntimeException('Could not resolve session');
        }
        return $session;
    }

    public function getSessionNullable(): ?Session
    {
        $session = $this->request->getAttribute(SessionMiddleware::ATTRIBUTE_SESSION);
        if ($session instanceof Session) {
            return $session;
        }
        return null;
    }

    public function getGame(): GameInterface
    {
        $game = $this->getGameNullable();
        if (null === $game) {
            throw new RuntimeException('Could not resolve game');
        }
        return $game;
    }

    public function getGameNullable(): ?GameInterface
    {
        $game = $this->request->getAttribute(GameResolvingMiddleware::ATTRIBUTE_GAME);
        if ($game instanceof GameInterface) {
            return $game;
        }
        return null;
    }

    public function getDraw(): Draw
    {
        $drawId = $this->request->getAttribute(PlayMiddleware::ATTRIBUTE_DRAW);
        if (!is_scalar($drawId)) {
            $drawId = 0;
        }
        $drawId = (int)$drawId;

        $draw = null;
        if ($drawId > 0) {
            $draw = Draw::fromId($drawId);
        }

        if (null !== $draw) {
            return $draw;
        }

        throw new RuntimeException('Could not resolve draw ID');
    }

    public function getCardOffset(): int
    {
        $cardOffset = $this->request->getAttribute(SpliceHandMiddleware::ATTRIBUTE_CARD_OFFSET);
        if (!is_scalar($cardOffset)) {
            $cardOffset = -1;
        }
        $cardOffset = (int)$cardOffset;

        if ($cardOffset > -1 && $cardOffset < Hand::HAND_SIZE) {
            return $cardOffset;
        }

        throw new RuntimeException('Could not resolve card offset');
    }

    public function getReplacementCard(): Card
    {
        $cardId = $this->request->getAttribute(SpliceHandMiddleware::ATTRIBUTE_NEW_CARD_ID);

        if (!is_scalar($cardId)) {
            $cardId = 0;
        }

        $cardId = (int)$cardId;
        $card = Card::fromId($cardId);
        return $card;
    }

    public function getPlayerName(): string
    {
        $playerName = $this->request->getAttribute(AuthenticationMiddleware::ATTRIBUTE_PLAYER_NAME);
        if (!is_scalar($playerName)) {
            return '';
        }
        return trim((string)$playerName);
    }

    public function getResolveGameFromSession(): bool
    {
        return $this->request->getAttribute(GameResolvingMiddleware::ATTRIBUTE_RESOLVE_GAME_FROM_SESSION, false);
    }

    public function getBetAmount(): int
    {
        $amount = $this->request->getAttribute(BetMiddleware::ATTRIBUTE_AMOUNT, 0);
        if (!is_scalar($amount)) {
            $amount = 0;
        }
        $amount = (int)$amount;
        if ($amount > 0) {
            return $amount;
        }
        throw new LogicException('Bet cannot be less than 1');
    }
}
