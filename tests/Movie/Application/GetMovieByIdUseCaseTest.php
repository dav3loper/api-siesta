<?php

namespace Siesta\Tests\Movie\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Movie\Application\GetMovieByIdUseCase;
use Siesta\Movie\Application\MovieResponse;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Tests\Fixtures\Movie\MovieMother;
use Siesta\Tests\Fixtures\Movie\VoteMother;
use Siesta\Vote\Domain\VoteRepository;

class GetMovieByIdUseCaseTest extends TestCase
{

    private GetMovieByIdUseCase $useCase;
    /** @var MovieRepository&MockObject  */
    private mixed $movieRepository;

    public function setUp(): void
    {
        $this->movieRepository = $this->createMock(MovieRepository::class);
        $this->useCase = new GetMovieByIdUseCase($this->movieRepository);
    }

    public function testWhenAskingForMovieReposAreCalled()
    {
        $id = 1;
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->expects(self::once())
            ->method('getById')
            ->with($id)
            ->willReturn($movie);

        $response = $this->useCase->execute($id);

        $expected = new MovieResponse($movie);
        self::assertEquals($expected, $response);

    }

}
