<?php

namespace Siesta\Tests\Movie\Application;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Movie\Application\GetMovieByIdUseCase;
use Siesta\Movie\Application\MovieWithVotesResponse;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Movie\Domain\VoteRepository;
use Siesta\Tests\Fixtures\Movie\MovieMother;
use Siesta\Tests\Fixtures\Movie\VoteMother;

class GetMovieByIdUseCaseTest extends TestCase
{

    private GetMovieByIdUseCase $useCase;
    /** @var MovieRepository&MockObject  */
    private mixed $movieRepository;

    /** @var VoteRepository&MockObject  */
    private mixed $voteRepository;

    public function setUp(): void
    {
        $this->movieRepository = $this->createMock(MovieRepository::class);
        $this->voteRepository = $this->createMock(VoteRepository::class);
        $this->useCase = new GetMovieByIdUseCase($this->movieRepository, $this->voteRepository);
    }

    public function testWhenAskingForMovieReposAreCalled()
    {
        $id = 1;
        $movie = MovieMother::create()->random()->build();
        $this->movieRepository->expects(self::once())
            ->method('getById')
            ->with($id)
            ->willReturn($movie);

        $voteCollection = VoteMother::create()->random()->build();
        $this->voteRepository->expects(self::once())
            ->method('getAllByMovieId')
            ->with($movie->id)
            ->willReturn($voteCollection);

        $response = $this->useCase->execute($id);

        $expected = new MovieWithVotesResponse($movie, $voteCollection);
        self::assertEquals($expected, $response);

    }

}
