<?php

namespace Siesta\App\Action\Movie;

use Siesta\App\Action\BaseAction;
use Siesta\Movie\Application\UpdateMovieMedia\UpdateMovieMediaRequest;
use Siesta\Movie\Application\UpdateMovieMedia\UpdateMovieMediaUseCase;
use Siesta\Shared\Exception\ValueNotValid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateMovieMediaAction extends BaseAction
{
    public function __construct(private readonly UpdateMovieMediaUseCase $updateMovieMediaUseCase)
    {
    }

    /**
     * @throws ValueNotValid
     */
    public function __invoke(Request $request, string $movieId): Response
    {
        $data = $request->toArray();
        $changesPoster = array_key_exists('poster', $data);
        $changesTrailer = array_key_exists('trailer', $data);
        if (!$changesPoster && !$changesTrailer) {
            throw new ValueNotValid('The request must contain a poster or a trailer');
        }

        $updateMovieMediaRequest = new UpdateMovieMediaRequest(
            $movieId,
            $changesPoster,
            $this->mediaValueOf($data, 'poster'),
            $changesTrailer,
            $this->mediaValueOf($data, 'trailer'),
        );
        $response = $this->updateMovieMediaUseCase->execute($updateMovieMediaRequest);

        return new JsonResponse($response->jsonSerialize());
    }

    /**
     * Null clears the media, a blank string is a client mistake rather than a way to clear it.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValueNotValid
     */
    private function mediaValueOf(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_string($value) || trim($value) === '') {
            throw new ValueNotValid("The $key must be a non-empty text or null");
        }

        return trim($value);
    }
}
