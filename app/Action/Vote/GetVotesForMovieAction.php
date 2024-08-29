<?php

namespace Siesta\App\Action\Vote;

use Siesta\App\Action\BaseAction;
use Siesta\Shared\Id\Id;
use Siesta\Vote\Application\LastVoted\GetVotesForMovieByGroupUseCase;
use Siesta\Vote\Application\LastVoted\GetVotesForMovieRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetVotesForMovieAction extends BaseAction
{
    public function __construct(private readonly GetVotesForMovieByGroupUseCase $getVotesForMovieUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $id = new Id($request->get('movieId'));
        $groupId = $request->headers->get('Group-Id') ? new Id($request->headers->get('Group-Id')) : null;
        $request = new GetVotesForMovieRequest($id, $groupId);
        $response = $this->getVotesForMovieUseCase->execute($request);

        return new Response(json_encode($response));

    }

}