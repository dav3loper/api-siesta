<?php

namespace Siesta\App\Action\Movie;

use Siesta\App\Action\BaseAction;
use Siesta\Movie\Application\UpdateMovieAlias\UpdateMovieAliasRequest;
use Siesta\Movie\Application\UpdateMovieAlias\UpdateMovieAliasUseCase;
use Siesta\Shared\Exception\ValueNotValid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateMovieAliasAction extends BaseAction
{
    private const MAX_ALIAS_LENGTH = 256;

    public function __construct(private readonly UpdateMovieAliasUseCase $updateMovieAliasUseCase)
    {
    }

    /**
     * @throws ValueNotValid
     */
    public function __invoke(Request $request, string $movieId): Response
    {
        $data = $request->toArray();
        if (!array_key_exists('alias', $data)) {
            throw new ValueNotValid('The request must contain an alias');
        }

        $updateMovieAliasRequest = new UpdateMovieAliasRequest($movieId, $this->aliasValueOf($data['alias']));
        $response = $this->updateMovieAliasUseCase->execute($updateMovieAliasRequest);

        return new JsonResponse($response->jsonSerialize());
    }

    /**
     * Null clears the alias, a blank string is a client mistake rather than a way to clear it.
     * An alias longer than the column is the client's mistake too, so it is rejected here
     * instead of reaching the database as a 500.
     *
     * @throws ValueNotValid
     */
    private function aliasValueOf(mixed $alias): ?string
    {
        if ($alias === null) {
            return null;
        }
        if (!is_string($alias) || trim($alias) === '') {
            throw new ValueNotValid('The alias must be a non-empty text or null');
        }
        $alias = trim($alias);
        if (mb_strlen($alias) > self::MAX_ALIAS_LENGTH) {
            throw new ValueNotValid('The alias must be ' . self::MAX_ALIAS_LENGTH . ' characters or less');
        }

        return $alias;
    }
}
