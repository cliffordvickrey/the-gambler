<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Psr\Container\ContainerInterface;

ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

require_once('../vendor/autoload.php');

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../app/container.php';

/** @var ProbabilityServiceInterface $probabilityService */
$probabilityService = $container->get(ProbabilityServiceInterface::class);

$meanPayout = $probabilityService->getMeanHighestPayout();
echo sprintf('The mean highest payout of every possible hand of video poker is %g%s', $meanPayout, PHP_EOL);

$minPayout = $probabilityService->getMinHighestPayout();
echo sprintf('The minimum highest payout of every possible hand of video poker is %g%s', $minPayout, PHP_EOL);

$stDev = $probabilityService->getStandardDeviationOfHighestPayout();
echo sprintf('The st. dev. of the highest payout of every possible hand of video poker is %g%s', $stDev, PHP_EOL);

$logMeanPayout = $probabilityService->getLogMeanHighestPayout();
echo sprintf(
    'The log-transformed mean highest payout of every possible hand of video poker is %g%s',
    $logMeanPayout,
    PHP_EOL
);

$logStDev = $probabilityService->getLogStandardDeviationOfHighestPayout();
echo sprintf(
    'The log-transformed st. dev. of the highest payout of every possible hand of video poker is %g%s',
    $logStDev,
    PHP_EOL
);

