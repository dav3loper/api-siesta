<?php

namespace Siesta\Tests\Movie\Application\UpdateMovieAlias;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Movie\Application\UpdateMovieAlias\UpdateMovieAliasRequest;
use Siesta\Movie\Application\UpdateMovieAlias\UpdateMovieAliasUseCase;
use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Tests\Fixtures\Movie\MovieMother;

class UpdateMovieAliasUseCaseTest extends TestCase
{
    private UpdateMovieAliasUseCase $useCase;
    /** @var MovieRepository&MockObject */
    private mixed $movieRepository;

    public function setUp(): void
    {
        $this->movieRepository = $this->createMock(MovieRepository::class);
        $this->useCase = new UpdateMovieAliasUseCase($this->movieRepository);
    }

    #[Test]
    public function whenAnAliasIsSentThenItReplacesTheCurrentOne(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);
        $this->movieRepository->expects(self::once())
            ->method('updateAlias')
            ->with(self::callback(function (Movie $updatedMovie) {
                self::assertSame('La de los pingüinos', $updatedMovie->alias);

                return true;
            }));

        $response = $this->useCase->execute(
            new UpdateMovieAliasRequest($movie->id->id, 'La de los pingüinos')
        );

        self::assertSame('La de los pingüinos', $response->movie->alias);
    }

    #[Test]
    public function whenTheAliasIsClearedThenTheMovieHasNoAlias(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);
        $this->movieRepository->expects(self::once())
            ->method('updateAlias')
            ->with(self::callback(function (Movie $updatedMovie) {
                self::assertNull($updatedMovie->alias);

                return true;
            }));

        $response = $this->useCase->execute(
            new UpdateMovieAliasRequest($movie->id->id, null)
        );

        self::assertNull($response->movie->alias);
    }

    #[Test]
    public function whenTheAliasChangesThenTheRestOfTheMovieIsUntouched(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);

        $response = $this->useCase->execute(
            new UpdateMovieAliasRequest($movie->id->id, 'La de los pingüinos')
        );

        $updatedMovie = $response->movie;
        self::assertSame($movie->title, $updatedMovie->title);
        self::assertSame($movie->poster, $updatedMovie->poster);
        self::assertSame($movie->trailer_id, $updatedMovie->trailer_id);
        self::assertSame($movie->sessions, $updatedMovie->sessions);
        self::assertSame($movie->getVoteCollection(), $updatedMovie->getVoteCollection());
    }

    #[Test]
    public function whenTheAliasChangesThenTheMediaLocksStayAsTheyWere(): void
    {
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->method('getById')->with($movie->id->id)->willReturn($movie);

        $response = $this->useCase->execute(
            new UpdateMovieAliasRequest($movie->id->id, 'La de los pingüinos')
        );

        self::assertSame($movie->poster_locked, $response->movie->poster_locked);
        self::assertSame($movie->trailer_locked, $response->movie->trailer_locked);
    }
}
