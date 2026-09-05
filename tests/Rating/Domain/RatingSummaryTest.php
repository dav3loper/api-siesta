<?php

namespace Siesta\Tests\Rating\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Siesta\Rating\Domain\RatingSummary;

class RatingSummaryTest extends TestCase
{
    #[Test]
    public function whenTheAverageHasManyDecimalsThenItIsRoundedToOne(): void
    {
        self::assertEquals(4.3, (new RatingSummary(4.2857142857, 7, 5))->average);
    }

    #[Test]
    public function whenNobodyHasRatedThenThereIsNoAverageAndNoOwnScore(): void
    {
        $summary = RatingSummary::withoutRatings();

        self::assertNull($summary->average);
        self::assertEquals(0, $summary->count);
        self::assertNull($summary->userScore);
    }
}
