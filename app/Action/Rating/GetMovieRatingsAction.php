<?php

namespace Siesta\App\Action\Rating;

use Siesta\App\Action\BaseAction;
use Siesta\Rating\Application\GetMovieRatings\GetMovieRatingsRequest;
use Siesta\Rating\Application\GetMovieRatings\GetMovieRatingsUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetMovieRatingsAction extends BaseAction
{
    public function __construct(private readonly GetMovieRatingsUseCase $getMovieRatingsUseCase)
    {
    }

    public function __invoke(Request $request, string $filmFestivalId): Response
    {
        $getMovieRatingsRequest = new GetMovieRatingsRequest(
            $filmFestivalId,
            $request->headers->get('User-Id'),
        );

        $response = $this->getMovieRatingsUseCase->execute($getMovieRatingsRequest);

        return new JsonResponse($response->jsonSerialize(), Response::HTTP_OK);
    }
}
