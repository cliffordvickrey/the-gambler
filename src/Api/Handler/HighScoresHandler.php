<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Api\Handler;

use Cliffordvickrey\TheGambler\Api\Response\JsonResponse;
use Cliffordvickrey\TheGambler\Domain\Game\Collection\HighScores;
use Cliffordvickrey\TheGambler\Domain\Game\Entity\GameInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\PlayerRating;
use Cliffordvickrey\TheGambler\Infrastructure\Http\ServerRequestDecorator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function sprintf;

class HighScoresHandler implements RequestHandlerInterface
{
    private $highScoresRepository;

    public function __construct(HighScoresRepositoryInterface $highScoresRepository)
    {
        $this->highScoresRepository = $highScoresRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestDecorator = new ServerRequestDecorator($request);
        $highScores = $this->highScoresRepository->get();
        $game = $requestDecorator->getGameNullable();
        list ($playerRating, $rank) = self::getRankAndPlayerRating($highScores, $game);

        $payload = [
            'highScores' => $highScores,
            'rank' => $rank,
            'playerRating' => $playerRating
        ];

        return new JsonResponse($payload);
    }

    private static function getRankAndPlayerRating(HighScores $highScores, ?GameInterface $game): array
    {
        if (null === $game) {
            return [null, null];
        }


        $meta = $game->getMeta();

        if ($meta->getCheated()) {
            return ['Billy Mitchell', 'cheater'];
        }

        $score = $meta->getScore();
        $playerRating = (string)(new PlayerRating($score));

        $gameId = (string)$game->getId();
        foreach ($highScores as $i => $highScore) {
            if ((string)$highScore->getGameId() === $gameId) {
                return [$playerRating, sprintf('%d', ((int)$i) + 1)];
            }
        }

        return [$playerRating, 'unranked'];
    }

}
