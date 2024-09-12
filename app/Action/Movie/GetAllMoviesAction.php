<?php

namespace Siesta\App\Action\Movie;

use Siesta\App\Action\BaseAction;
use Siesta\Movie\Application\GetAllMoviesUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllMoviesAction extends BaseAction
{

    public function __construct(private readonly GetAllMoviesUseCase $useCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $id = $request->get('filmFestivalId');
        $response = $this->useCase->execute($id);

        return new Response(json_encode($response));

    }



}