<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Game\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\Format;
use Cliffordvickrey\TheGambler\Domain\Utility\Math;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use UnexpectedValueException;
use function is_float;
use function serialize;
use function sprintf;
use function unserialize;

final class MoveSkill implements PortableInterface
{
    private $expectedPayout;
    private $optimalDraw;
    private $optimalExpectedPayout;

    public function __construct(
        float $expectedPayout,
        Draw $optimalDraw,
        float $optimalExpectedPayout
    )
    {
        $this->expectedPayout = $expectedPayout;
        $this->optimalDraw = $optimalDraw;
        $this->optimalExpectedPayout = $optimalExpectedPayout;
    }

    public function __clone()
    {
        $this->optimalDraw = clone $this->optimalDraw;
    }

    /**
     * @return float
     */
    public function getExpectedPayout(): float
    {
        return $this->expectedPayout;
    }

    /**
     * @return float
     */
    public function getOptimalExpectedPayout(): float
    {
        return $this->optimalExpectedPayout;
    }

    public function jsonSerialize(): array
    {
        return [
            'expectedPayout' => Format::dollarFormat($this->expectedPayout, 2),
            'optimalDraw' => $this->optimalDraw,
            'optimalExpectedPayout' => Format::dollarFormat($this->optimalExpectedPayout, 2),
            'efficiency' => Format::percentFormatRounded($this->getEfficiency())
        ];
    }

    /**
     * @return float
     */
    public function getEfficiency(): float
    {
        if (0.0 === $this->expectedPayout) {
            return 0.0;
        }

        return Math::safeDivision($this->expectedPayout, $this->optimalExpectedPayout);
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => [Draw::class]]);

        $expectedPayout = $unSerialized['expectedPayout'] ?? null;
        if (!is_float($expectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $optimalDraw = $unSerialized['optimalDraw'] ?? null;
        if (!($optimalDraw instanceof Draw)) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s', Draw::class));
        }

        $optimalExpectedPayout = $unSerialized['optimalExpectedPayout'] ?? null;
        if (!is_float($optimalExpectedPayout)) {
            throw new UnexpectedValueException('Expected float');
        }

        $this->expectedPayout = $expectedPayout;
        $this->optimalDraw = $optimalDraw;
        $this->optimalExpectedPayout = $optimalExpectedPayout;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize([
            'expectedPayout' => $this->expectedPayout,
            'optimalDraw' => $this->optimalDraw,
            'optimalExpectedPayout' => $this->optimalExpectedPayout
        ]);
    }

    /**
     * @return Draw
     */
    public function getOptimalDraw(): Draw
    {
        return $this->optimalDraw;
    }
}