<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Session;

use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;

interface SessionManagerInterface
{
    public function authenticate(Player $player): void;

    public function destroy(): void;

    public function getAuthenticatedSession(): Session;

    public function isAuthenticated(): bool;

    public function isStarted(): bool;

    public function start(): void;

    public function writeClose(): void;
}
