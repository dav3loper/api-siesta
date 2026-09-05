<?php

namespace Siesta\Tests\Rating\Application\RateMovie;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Rating\Application\RateMovie\RateMovieRequest;
use Siesta\Rating\Application\RateMovie\RateMovieUseCase;
use Siesta\Rating\Domain\MovieFinder;
use Siesta\Rating\Domain\Rating;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Rating\Domain\RatingScore;
use Siesta\Rating\Domain\RatingSummary;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\Id\Id;

class RateMovieUseCaseTest extends TestCase
{
    private RateMovieUseCase $useCase;
    /** @var RatingRepository&MockObject */
    private mixed $ratingRepository;
    /** @var MovieFinder&MockObject */
    private mixed $movieFinder;

    public function setUp(): void
    {
        $this->ratingRepository = $this->createMock(RatingRepository::class);
        $this->movieFinder = $this->createMock(MovieFinder::class);
        $this->movieFinder->method('exists')->willReturn(true);
        $this->useCase = new RateMovieUseCase($this->ratingRepository, $this->movieFinder);
    }

    #[Test]
    public function whenScoreIsValidThenUpsertsRating(): void
    {
        $expectedRating = new Rating(new Id('1'), new Id('9'), new RatingScore(4));
        $this->ratingRepository->expects(self::once())
            ->method('upsert')
            ->with($expectedRating);
        $this->ratingRepository->method('summaryOf')->willReturn(RatingSummary::withoutRatings());

        $this->useCase->execute(new RateMovieRequest('1', '9', 4));
    }

    #[Test]
    public function whenScoreIsOutOfRangeThenThrowsValueNotValid(): void
    {
        $this->ratingRepository->expects(self::never())->method('upsert');

        $this->expectException(ValueNotValid::class);

        $this->useCase->execute(new RateMovieRequest('1', '9', 7));
    }

    #[Test]
    public function whenTheMovieDoesNotExistThenThrowsDataNotFoundWithoutStoringAnything(): void
    {
        $movieFinder = $this->createMock(MovieFinder::class);
        $movieFinder->expects(self::once())
            ->method('exists')
            ->with(new Id('404'))
            ->willReturn(false);
        $this->ratingRepository->expects(self::never())->method('upsert');

        $this->expectException(DataNotFound::class);

        (new RateMovieUseCase($this->ratingRepository, $movieFinder))
            ->execute(new RateMovieRequest('1', '404', 4));
    }

    #[Test]
    public function whenTheMovieIsRatedThenAnswersWithTheRecalculatedRatings(): void
    {
        $this->ratingRepository->expects(self::once())
            ->method('summaryOf')
            ->with(new Id('9'), new Id('1'))
            ->willReturn(new RatingSummary(4.5, 8, 5));

        $response = $this->useCase->execute(new RateMovieRequest('1', '9', 5));

        self::assertEquals(
            ['average_rating' => 4.5, 'ratings_count' => 8, 'user_rating' => 5],
            $response->jsonSerialize()
        );
    }
}
