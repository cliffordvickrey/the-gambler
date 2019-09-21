<?php

declare(strict_types=1);

namespace Tests\Cliffordvickrey\TheGambler\Domain\Rules;

use Cliffordvickrey\TheGambler\Domain\Enum\HandType;
use Cliffordvickrey\TheGambler\Domain\Rules\Rules;
use Cliffordvickrey\TheGambler\Domain\Rules\RulesInterface;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

class RulesTest extends TestCase
{
    public function testFromDefaults(): void
    {
        $rules = Rules::fromDefaults();
        $this->assertEquals(Rules::DEFAULT_BET_AMOUNT, $rules->getBetAmount());
        $this->assertEquals(Rules::DEFAULT_STARTING_PURSE, $rules->getStartingPurse());

        $defaultHandPayouts = Rules::getDefaultHandPayouts();
        foreach ($defaultHandPayouts as $handTypeScalar => $defaultHandPayout) {
            $handType = new HandType($handTypeScalar);
            $handPayout = $rules->getPayoutAmount($handType);
            $this->assertEquals($defaultHandPayout, $handPayout);
        }
    }

    public function testSerialize(): void
    {
        $rules = Rules::fromDefaults();
        $serialized = serialize($rules);
        /** @var RulesInterface $unSerialized */
        $unSerialized = unserialize($serialized);

        $this->assertEquals(Rules::DEFAULT_BET_AMOUNT, $unSerialized->getBetAmount());
        $this->assertEquals(Rules::DEFAULT_STARTING_PURSE, $unSerialized->getStartingPurse());

        $defaultHandPayouts = Rules::getDefaultHandPayouts();
        foreach ($defaultHandPayouts as $handTypeScalar => $defaultHandPayout) {
            $handType = new HandType($handTypeScalar);
            $handPayout = $unSerialized->getPayoutAmount($handType);
            $this->assertEquals($defaultHandPayout, $handPayout);
        }
    }

    public function testConstructInvalidBetAmount(): void
    {
        $defaults = Rules::getDefaultHandPayouts();
        $this->expectExceptionMessage('Bet amount must not be negative');
        new Rules(-1, 200, $defaults);
    }

    public function testConstructInvalidStartingPurse(): void
    {
        $defaults = Rules::getDefaultHandPayouts();
        $this->expectExceptionMessage('Starting purse cannot be less than bet amount');
        new Rules(5, 4, $defaults);
    }

    public function testConstructMissingPayout(): void
    {
        $defaults = Rules::getDefaultHandPayouts();
        unset($defaults[HandType::ROYAL_FLUSH]);
        $this->expectExceptionMessage('No hand payout amount provided for royalFlush');
        new Rules(5, 200, $defaults);
    }

    public function testConstructNonNumericPayout(): void
    {
        $defaults = Rules::getDefaultHandPayouts();
        $defaults[HandType::ROYAL_FLUSH] = 'blah';
        $this->expectExceptionMessage('Hand payout for royalFlush must be numeric');
        new Rules(5, 200, $defaults);
    }

    public function testConstructNegativePayout(): void
    {
        $defaults = Rules::getDefaultHandPayouts();
        $defaults[HandType::ROYAL_FLUSH] = -1;
        $this->expectExceptionMessage('Hand payout for royalFlush cannot be negative');
        new Rules(5, 200, $defaults);
    }

    public function testConstructMisMatchedPayout(): void
    {
        $defaults = Rules::getDefaultHandPayouts();
        $defaults[HandType::ROYAL_FLUSH] = 250;
        $this->expectExceptionMessage('Hand payout for royalFlush must be greater than hand payout for straightFlush');
        new Rules(5, 200, $defaults);
    }
}