<?php

namespace Siesta\App\Action\Vote;

use Siesta\App\Action\BaseAction;
use Siesta\Shared\Id\Id;
use Siesta\Vote\Application\PendingVotes\GetPendingVotesCountUseCase;
use Siesta\Vote\Application\PendingVotes\GetPendingVotesCountUseCaseRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetPendingVotesCountAction extends BaseAction
{

    public function __construct(private readonly GetPendingVotesCountUseCase $useCase)
    {
    }

    public function __invoke(Request $request, int $filmFestivalId): Response
    {
        $userId = $request->headers->get('User-Id');
        $useCaseRequest = new GetPendingVotesCountUseCaseRequest(new Id($filmFestivalId), new Id($userId));
        $response = $this->useCase->execute($useCaseRequest);

        return new JsonResponse(['count' => $response->count]);
    }

}
