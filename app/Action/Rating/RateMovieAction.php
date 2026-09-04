<?php

namespace Siesta\App\Action\Rating;

use Siesta\App\Action\BaseAction;
use Siesta\Rating\Application\RateMovie\RateMovieRequest;
use Siesta\Rating\Application\RateMovie\RateMovieUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateMovieAction extends BaseAction
{
    public function __construct(private readonly RateMovieUseCase $rateMovieUseCase)
    {
    }

    public function __invoke(Request $request, string $movieId): Response
    {
        $userId = $request->headers->get('User-Id');
        $data = $request->toArray();

        $rateMovieRequest = new RateMovieRequest(
            $userId,
            $movieId,
            $data['score'],
        );
        $this->rateMovieUseCase->execute($rateMovieRequest);

        return new JsonResponse([], Response::HTTP_CREATED);
    }
}
