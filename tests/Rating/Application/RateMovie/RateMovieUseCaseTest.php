<?php

namespace Siesta\Tests\Rating\Application\RateMovie;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Rating\Application\RateMovie\RateMovieRequest;
use Siesta\Rating\Application\RateMovie\RateMovieUseCase;
use Siesta\Rating\Domain\Rating;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Rating\Domain\RatingScore;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\Id\Id;

class RateMovieUseCaseTest extends TestCase
{
    private RateMovieUseCase $useCase;
    /** @var RatingRepository&MockObject */
    private mixed $ratingRepository;

    public function setUp(): void
    {
        $this->ratingRepository = $this->createMock(RatingRepository::class);
        $this->useCase = new RateMovieUseCase($this->ratingRepository);
    }

    #[Test]
    public function whenScoreIsValidThenUpsertsRating(): void
    {
        $expectedRating = new Rating(new Id('1'), new Id('9'), new RatingScore(4));
        $this->ratingRepository->expects(self::once())
            ->method('upsert')
            ->with($expectedRating);

        $this->useCase->execute(new RateMovieRequest('1', '9', 4));
    }

    #[Test]
    public function whenScoreIsOutOfRangeThenThrowsValueNotValid(): void
    {
        $this->ratingRepository->expects(self::never())->method('upsert');

        $this->expectException(ValueNotValid::class);

        $this->useCase->execute(new RateMovieRequest('1', '9', 7));
    }
}
