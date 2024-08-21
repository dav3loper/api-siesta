<?php

namespace Siesta\App\Action\Movie;

use Siesta\App\Action\BaseAction;
use Siesta\Movie\Application\GetMovieByIdUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetMovieByIdAction extends BaseAction
{
    public function __construct(private readonly GetMovieByIdUseCase $getMovieByIdUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $id = $request->get('id');
        $response = $this->getMovieByIdUseCase->execute($id);

        return new Response(json_encode($response));

    }

}