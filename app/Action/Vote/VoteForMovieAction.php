<?php

namespace Siesta\App\Action\Vote;

use Siesta\App\Action\BaseAction;
use Siesta\Vote\Application\Vote\VoteForMovieUseCase;
use Siesta\Vote\Application\Vote\VoteRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VoteForMovieAction extends BaseAction
{
    public function __construct(private readonly VoteForMovieUseCase $voteForMovieUseCase)
    {
    }

    public function __invoke(Request $request, string $movieId): Response
    {
        $voteDataList = $request->toArray();
        $groupId = $request->headers->get('Group-Id');


        array_map(function ($voteData) use ($movieId, $groupId) {
            $voteRequest = new VoteRequest(
                $voteData['user_id'],
                $movieId,
                $voteData['score'] ?? null,
                $groupId ?? null
            );
            $this->voteForMovieUseCase->execute($voteRequest);

        }, $voteDataList);

        return new JsonResponse([], Response::HTTP_CREATED);
    }


}