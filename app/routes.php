<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Handler\GameHandler;
use Cliffordvickrey\TheGambler\Api\Handler\HighScoresHandler;
use Cliffordvickrey\TheGambler\Api\Handler\RulesHandler;
use Cliffordvickrey\TheGambler\Api\Middleware\AuthenticationMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\BetMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\CheatMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\DestroyGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\NewGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\PlayMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SaveGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SpliceHandMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\UpdateHighScoresMiddleware;
use Slim\App;

return function (App $app) {
    /** @var array $config */
    $config = $app->getContainer()->get('config');
    $apiRoot = $config['apiRoot'] ?? '';

    $app->get($apiRoot . '/', GameHandler::class);

    $app->get($apiRoot . '/rules', RulesHandler::class);

    $app->get($apiRoot . '/high-scores[/{gameId}]', HighScoresHandler::class);

    $app->post($apiRoot . '/authenticate/{playerName}', GameHandler::class)
        ->add(AuthenticationMiddleware::class);

    $app->post($apiRoot . '/new-game', GameHandler::class)
        ->add(NewGameMiddleware::class);

    $app->post($apiRoot . '/bet/{gameId}/{amount:\d+}', GameHandler::class)
        ->add(UpdateHighScoresMiddleware::class)
        ->add(SaveGameMiddleware::class)
        ->add(BetMiddleware::class);

    $app->post($apiRoot . '/play/{gameId}/{draw:\d+}', GameHandler::class)
        ->add(UpdateHighScoresMiddleware::class)
        ->add(SaveGameMiddleware::class)
        ->add(PlayMiddleware::class);

    $app->post($apiRoot . '/cheat/{gameId}', GameHandler::class)
        ->add(SaveGameMiddleware::class)
        ->add(CheatMiddleware::class);

    $app->post($apiRoot . '/splice/{gameId}/{cardOffset:\d+}/{newCardId:\d+}', GameHandler::class)
        ->add(SaveGameMiddleware::class)
        ->add(SpliceHandMiddleware::class);

    $app->post($apiRoot . '/destroy/{gameId}', GameHandler::class)
        ->add(DestroyGameMiddleware::class);
};
