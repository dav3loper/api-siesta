<?php

namespace Siesta\App\Action\Vote;

use Siesta\App\Action\BaseAction;
use Siesta\Shared\Id\Id;
use Siesta\Vote\Application\GetVotesForMovieUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetVotesForMovieAction extends BaseAction
{
    public function __construct(private readonly GetVotesForMovieUseCase $getVotesForMovieUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $id = new Id($request->get('movieId'));
        $response = $this->getVotesForMovieUseCase->execute($id);

        return new Response(json_encode($response));

    }

}