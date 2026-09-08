<?php

namespace Siesta\Tests\Movie\Application\UpdateMovieMedia;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Movie\Application\UpdateMovieMedia\UpdateMovieMediaRequest;
use Siesta\Movie\Application\UpdateMovieMedia\UpdateMovieMediaUseCase;
use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Tests\Fixtures\Movie\MovieMother;

class UpdateMovieMediaUseCaseTest extends TestCase
{
    private UpdateMovieMediaUseCase $useCase;
    /** @var MovieRepository&MockObject */
    private mixed $movieRepository;

    public function setUp(): void
    {
        $this->movieRepository = $this->createMock(MovieRepository::class);
        $this->useCase = new UpdateMovieMediaUseCase($this->movieRepository);
    }

    #[Test]
    public function whenTheTrailerIsClearedThenThePosterIsKept(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);
        $this->movieRepository->expects(self::once())
            ->method('updateMedia')
            ->with(self::callback(function (Movie $updatedMovie) use ($movie) {
                self::assertNull($updatedMovie->trailer_id);
                self::assertSame($movie->poster, $updatedMovie->poster);
                self::assertTrue($updatedMovie->trailer_locked);
                self::assertFalse($updatedMovie->poster_locked);

                return true;
            }));

        $response = $this->useCase->execute(
            new UpdateMovieMediaRequest($movie->id->id, false, null, true, null)
        );

        self::assertNull($response->movie->trailer_id);
    }

    #[Test]
    public function whenAPosterIsSentThenItReplacesTheCurrentOne(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);
        $this->movieRepository->expects(self::once())
            ->method('updateMedia')
            ->with(self::callback(function (Movie $updatedMovie) use ($movie) {
                self::assertSame('https://images.example/right-poster.jpg', $updatedMovie->poster);
                self::assertSame($movie->trailer_id, $updatedMovie->trailer_id);
                self::assertTrue($updatedMovie->poster_locked);
                self::assertFalse($updatedMovie->trailer_locked);

                return true;
            }));

        $response = $this->useCase->execute(
            new UpdateMovieMediaRequest($movie->id->id, true, 'https://images.example/right-poster.jpg', false, null)
        );

        self::assertSame('https://images.example/right-poster.jpg', $response->movie->poster);
    }

    #[Test]
    public function whenTheMediaChangesThenTheRestOfTheMovieIsUntouched(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);

        $response = $this->useCase->execute(
            new UpdateMovieMediaRequest($movie->id->id, true, null, true, null)
        );

        $updatedMovie = $response->movie;
        self::assertSame($movie->title, $updatedMovie->title);
        self::assertSame($movie->summary, $updatedMovie->summary);
        self::assertSame($movie->sessions, $updatedMovie->sessions);
        self::assertSame($movie->getVoteCollection(), $updatedMovie->getVoteCollection());
    }

    #[Test]
    public function whenBothMediaAreClearedThenBothStayLockedAgainstTheImporter(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);

        $response = $this->useCase->execute(
            new UpdateMovieMediaRequest($movie->id->id, true, null, true, null)
        );

        self::assertTrue($response->movie->poster_locked);
        self::assertTrue($response->movie->trailer_locked);
    }
}
