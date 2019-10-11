<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Session;

use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\GameId;
use Cliffordvickrey\TheGambler\Domain\Game\ValueObject\Player;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\PhpSerializer;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\SerializerInterface;
use RuntimeException;
use Throwable;
use function is_string;
use function session_destroy;
use function session_regenerate_id;
use function session_start;
use function session_status;
use function session_write_close;
use const PHP_SESSION_ACTIVE;

class SessionManager implements SessionManagerInterface
{
    const SESSION_KEY = 'poker';

    /** @var SerializerInterface */
    private $serializer;
    /** @var Session|null */
    private $session;

    public function __construct(?SerializerInterface $serializer = null)
    {
        if (null === $serializer) {
            $serializer = new PhpSerializer([GameId::class, Player::class, Session::class]);
        }
        $this->serializer = $serializer;
    }

    public function __destruct()
    {
        $this->writeClose();
    }

    public function writeClose(): void
    {
        if ($this->isStarted()) {
            $_SESSION[self::SESSION_KEY] = $this->serializer->serialize($this->session);
            session_write_close();
        }
    }

    public function isStarted(): bool
    {
        return PHP_SESSION_ACTIVE === session_status();
    }

    public function destroy(): void
    {
        $this->start();
        session_destroy();
    }

    public function start(): void
    {
        if (!$this->isStarted()) {
            session_start();
            $this->session = $this->unSerializeSession();
        }
    }

    private function unSerializeSession(): ?Session
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        $sessionString = $_SESSION[self::SESSION_KEY];

        if (!is_string($sessionString)) {
            return null;
        }

        try {
            $session = $this->serializer->unSerialize($sessionString);
        } catch (Throwable $e) {
            return null;
        }

        if ($session instanceof Session) {
            return $session;
        }

        return null;
    }

    public function authenticate(Player $player): void
    {
        $this->start();
        $this->session = new Session($player);
        session_regenerate_id();
    }

    /**
     * @return Session
     * @throws SessionException
     */
    public function getAuthenticatedSession(): Session
    {
        if (!$this->isAuthenticated()) {
            throw new SessionException('Player is not authenticated');
        }

        if (null === $this->session) {
            throw new RuntimeException('Authentication failed');
        }

        return $this->session;
    }

    public function isAuthenticated(): bool
    {
        $this->start();
        return null !== $this->session;
    }
}
