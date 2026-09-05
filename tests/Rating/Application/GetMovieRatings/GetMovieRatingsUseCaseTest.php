<?php

namespace Siesta\Tests\Rating\Application\GetMovieRatings;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Rating\Application\GetMovieRatings\GetMovieRatingsRequest;
use Siesta\Rating\Application\GetMovieRatings\GetMovieRatingsUseCase;
use Siesta\Rating\Domain\MovieRating;
use Siesta\Rating\Domain\MovieRatingCollection;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Rating\Domain\RatingSummary;
use Siesta\Shared\Id\Id;

class GetMovieRatingsUseCaseTest extends TestCase
{
    private GetMovieRatingsUseCase $useCase;
    /** @var RatingRepository&MockObject */
    private mixed $ratingRepository;

    public function setUp(): void
    {
        $this->ratingRepository = $this->createMock(RatingRepository::class);
        $this->useCase = new GetMovieRatingsUseCase($this->ratingRepository);
    }

    #[Test]
    public function whenTheEditionHasMoviesThenEachOneCarriesItsRatings(): void
    {
        $this->ratingRepository->expects(self::once())
            ->method('findAllByFilmFestivalId')
            ->with(new Id('9'), new Id('1'))
            ->willReturn(new MovieRatingCollection([
                new MovieRating(
                    new Id('412'),
                    'Titane',
                    'https://image.tmdb.org/t/p/w500/poster.jpg',
                    'Oficial Fantàstic',
                    9,
                    new RatingSummary(4.25, 4, 5)
                ),
            ]));

        $response = $this->useCase->execute(new GetMovieRatingsRequest('9', '1'));

        self::assertEquals([[
            'id' => 412,
            'title' => 'Titane',
            'poster' => 'https://image.tmdb.org/t/p/w500/poster.jpg',
            'section' => 'Oficial Fantàstic',
            'film_festival_id' => 9,
            'average_rating' => 4.3,
            'ratings_count' => 4,
            'user_rating' => 5,
        ]], $response->jsonSerialize());
    }

    #[Test]
    public function whenAMovieHasNoRatingsThenTheAverageAndTheOwnRatingAreNull(): void
    {
        $this->ratingRepository->method('findAllByFilmFestivalId')
            ->willReturn(new MovieRatingCollection([
                new MovieRating(new Id('7'), 'Sin valorar', '', null, 9, RatingSummary::withoutRatings()),
            ]));

        $row = $this->useCase->execute(new GetMovieRatingsRequest('9', '1'))->jsonSerialize()[0];

        self::assertNull($row['average_rating']);
        self::assertEquals(0, $row['ratings_count']);
        self::assertNull($row['user_rating']);
    }

    #[Test]
    public function whenTheUserHasNotRatedAMovieRatedByOthersThenOnlyTheOwnRatingIsNull(): void
    {
        $this->ratingRepository->method('findAllByFilmFestivalId')
            ->willReturn(new MovieRatingCollection([
                new MovieRating(new Id('7'), 'Valorada por otros', '', null, 9, new RatingSummary(3.0, 2, null)),
            ]));

        $row = $this->useCase->execute(new GetMovieRatingsRequest('9', '1'))->jsonSerialize()[0];

        self::assertEquals(3.0, $row['average_rating']);
        self::assertEquals(2, $row['ratings_count']);
        self::assertNull($row['user_rating']);
    }

    #[Test]
    public function whenTheEditionHasNoMoviesThenTheListIsEmpty(): void
    {
        $this->ratingRepository->method('findAllByFilmFestivalId')
            ->willReturn(new MovieRatingCollection([]));

        self::assertEquals([], $this->useCase->execute(new GetMovieRatingsRequest('9', '1'))->jsonSerialize());
    }
}
