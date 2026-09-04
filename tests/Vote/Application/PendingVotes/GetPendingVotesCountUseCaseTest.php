<?php

namespace Siesta\Tests\Vote\Application\PendingVotes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Shared\Id\Id;
use Siesta\Vote\Application\PendingVotes\GetPendingVotesCountUseCase;
use Siesta\Vote\Application\PendingVotes\GetPendingVotesCountUseCaseRequest;
use Siesta\Vote\Application\PendingVotes\PendingVotesCountResponse;
use Siesta\Vote\Domain\VoteRepository;

class GetPendingVotesCountUseCaseTest extends TestCase
{

    private GetPendingVotesCountUseCase $useCase;
    /** @var VoteRepository&MockObject */
    private mixed $voteRepository;

    public function setUp(): void
    {
        $this->voteRepository = $this->createMock(VoteRepository::class);
        $this->useCase = new GetPendingVotesCountUseCase($this->voteRepository);
    }

    #[Test]
    public function whenMoviesAreLeftToVoteThenReturnsTheirCount(): void
    {
        $userId = new Id('1');
        $filmFestivalId = new Id('9');
        $this->voteRepository->expects(self::once())
            ->method('countMoviesLeftToVote')
            ->with($userId, $filmFestivalId)
            ->willReturn(5);

        $response = $this->useCase->execute(new GetPendingVotesCountUseCaseRequest($filmFestivalId, $userId));

        self::assertEquals(new PendingVotesCountResponse(5), $response);
    }

    #[Test]
    public function whenNoMoviesAreLeftToVoteThenReturnsZero(): void
    {
        $userId = new Id('1');
        $filmFestivalId = new Id('9');
        $this->voteRepository->expects(self::once())
            ->method('countMoviesLeftToVote')
            ->with($userId, $filmFestivalId)
            ->willReturn(0);

        $response = $this->useCase->execute(new GetPendingVotesCountUseCaseRequest($filmFestivalId, $userId));

        self::assertEquals(new PendingVotesCountResponse(0), $response);
    }

}
