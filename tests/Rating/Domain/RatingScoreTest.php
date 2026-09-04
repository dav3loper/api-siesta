<?php

namespace Siesta\Tests\Rating\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Siesta\Rating\Domain\RatingScore;
use Siesta\Shared\Exception\ValueNotValid;

class RatingScoreTest extends TestCase
{
    #[Test]
    public function whenScoreIsWithinRangeThenReturnsItsValue(): void
    {
        $ratingScore = new RatingScore(3);

        self::assertEquals(3, $ratingScore->value());
    }

    #[Test]
    public function whenScoreIsBelowMinimumThenThrowsValueNotValid(): void
    {
        $this->expectException(ValueNotValid::class);

        new RatingScore(0);
    }

    #[Test]
    public function whenScoreIsAboveMaximumThenThrowsValueNotValid(): void
    {
        $this->expectException(ValueNotValid::class);

        new RatingScore(6);
    }
}
