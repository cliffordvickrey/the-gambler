<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Middleware;

use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHighScoresMiddleware implements MiddlewareInterface
{
    private $highScoreRepository;

    public function __construct(HighScoresRepositoryInterface $highScoresRepository)
    {
        $this->highScoreRepository = $highScoresRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $session = $requestDecorator->getSession();
        $game = $requestDecorator->getGame();
        $player = $session->getPlayer();
        $this->highScoreRepository->add($player, $game);
        return $handler->handle($request);
    }
}
