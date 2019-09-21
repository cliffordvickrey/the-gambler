<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Handler\HighScoresHandler;
use Cliffordvickrey\TheGambler\Api\Handler\RulesHandler;
use Cliffordvickrey\TheGambler\Api\Middleware\AuthenticationMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\DestroyGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\ErrorHandlingMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\GarbageCollectionMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\SaveGameMiddleware;
use Cliffordvickrey\TheGambler\Api\Middleware\UpdateHighScoresMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\HandTypeResolver\HandTypeResolverInterface;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\GameCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\HighScoresCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\ProbabilityTreeCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Handler\HighScoresHandlerFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Handler\RulesHandlerFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware\AuthenticationMiddlewareFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware\DestroyGameMiddlewareFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware\ErrorHandlingMiddlewareFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware\GarbageCollectionMiddlewareFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware\SaveGameMiddlewareFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware\UpdateHighScoresMiddlewareFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Game\Repository\GameRepositoryFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Game\Repository\HighScoresRepositoryFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\HandTypeResolver\HandTypeResolverFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Probability\Service\ProbabilityServiceFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Rules\RulesFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Cache\GameCacheFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Cache\HighScoresCacheFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Cache\ProbabilityTreeCacheFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Serializer\SerializerFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Session\SessionManagerFactory;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\SerializerInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Session\SessionManagerInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return (function () {
    $containerBuilder = new ContainerBuilder();

    $config = require __DIR__ . '/config.php';

    $containerBuilder->addDefinitions([
        'config' => $config,
        AuthenticationMiddleware::class => function (ContainerInterface $container) {
            $factory = new AuthenticationMiddlewareFactory();
            return $factory($container);
        },
        DestroyGameMiddleware::class => function (ContainerInterface $container) {
            $factory = new DestroyGameMiddlewareFactory();
            return $factory($container);
        },
        ErrorHandlingMiddleware::class => function (ContainerInterface $container) {
            $factory = new ErrorHandlingMiddlewareFactory();
            return $factory($container);
        },
        GarbageCollectionMiddleware::class => function (ContainerInterface $container) {
            $factory = new GarbageCollectionMiddlewareFactory();
            return $factory($container);
        },
        GameCacheInterface::class => function (ContainerInterface $container) {
            $factory = new GameCacheFactory();
            return $factory($container);
        },
        GameRepositoryInterface::class => function (ContainerInterface $container) {
            $factory = new GameRepositoryFactory();
            return $factory($container);
        },
        HandTypeResolverInterface::class => function (ContainerInterface $container) {
            $factory = new HandTypeResolverFactory();
            return $factory($container);
        },
        HighScoresCacheInterface::class => function (ContainerInterface $container) {
            $factory = new HighScoresCacheFactory();
            return $factory($container);
        },
        HighScoresHandler::class => function (ContainerInterface $container) {
            $factory = new HighScoresHandlerFactory();
            return $factory($container);
        },
        HighScoresRepositoryInterface::class => function (ContainerInterface $container) {
            $factory = new HighScoresRepositoryFactory();
            return $factory($container);
        },
        ProbabilityTreeCacheInterface::class => function (ContainerInterface $container) {
            $factory = new ProbabilityTreeCacheFactory();
            return $factory($container);
        },
        ProbabilityServiceInterface::class => function (ContainerInterface $container) {
            $factory = new ProbabilityServiceFactory();
            return $factory($container);
        },
        RulesHandler::class => function (ContainerInterface $container) {
            $factory = new RulesHandlerFactory();
            return $factory($container);
        },
        RulesInterface::class => function (ContainerInterface $container) {
            $factory = new RulesFactory();
            return $factory($container);
        },
        SaveGameMiddleware::class => function (ContainerInterface $container) {
            $factory = new SaveGameMiddlewareFactory();
            return $factory($container);
        },
        SessionManagerInterface::class => function (ContainerInterface $container) {
            $factory = new SessionManagerFactory();
            return $factory($container);
        },
        SerializerInterface::class => function (ContainerInterface $container) {
            $factory = new SerializerFactory();
            return $factory($container);
        },
        UpdateHighScoresMiddleware::class => function (ContainerInterface $container) {
            $factory = new UpdateHighScoresMiddlewareFactory();
            return $factory($container);
        }
    ]);

    return $containerBuilder->build();
})();
