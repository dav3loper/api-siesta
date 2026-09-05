<?php

namespace Siesta\App\Action\Rating;

use Siesta\App\Action\BaseAction;
use Siesta\Rating\Application\RateMovie\RateMovieRequest;
use Siesta\Rating\Application\RateMovie\RateMovieUseCase;
use Siesta\Shared\Exception\ValueNotValid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateMovieAction extends BaseAction
{
    public function __construct(private readonly RateMovieUseCase $rateMovieUseCase)
    {
    }

    /**
     * @throws ValueNotValid
     */
    public function __invoke(Request $request, string $movieId): Response
    {
        $userId = $request->headers->get('User-Id');
        $data = $request->toArray();

        $rateMovieRequest = new RateMovieRequest(
            $userId,
            $movieId,
            $this->scoreOf($data),
        );
        $response = $this->rateMovieUseCase->execute($rateMovieRequest);

        return new JsonResponse($response->jsonSerialize(), Response::HTTP_CREATED);
    }

    /**
     * A body without a score used to reach the use case as null and die there as a 500.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValueNotValid
     */
    private function scoreOf(array $data): int
    {
        $score = filter_var($data['score'] ?? null, FILTER_VALIDATE_INT);
        if ($score === false) {
            throw new ValueNotValid('The score of a rating must be a whole number');
        }

        return $score;
    }
}
