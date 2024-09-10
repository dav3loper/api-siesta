<?php

namespace Siesta\App\Action\Vote;

use Siesta\App\Action\BaseAction;
use Siesta\Shared\Id\Id;
use Siesta\Vote\Application\LastVoted\GetNextMovieToVoteUseCase;
use Siesta\Vote\Application\LastVoted\GetNextMovieToVoteUseCaseRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetNextMovieToVoteAction extends BaseAction
{

    public function __construct(private readonly GetNextMovieToVoteUseCase $useCase)
    {
    }

    public function __invoke(Request $request, int $filmFestivalId): Response
    {
        $userId = $request->headers->get('User-Id');
        $request = new GetNextMovieToVoteUseCaseRequest(new Id($filmFestivalId), new Id($userId));
        $response = $this->useCase->execute($request);

        return new JsonResponse($response);

    }

}