<?php

namespace Siesta\App\Action\FilmFestival;

use Siesta\App\Action\BaseAction;
use Siesta\FilmFestival\Application\GetAllFilmFestivalsUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllFilmFestivalsAction extends BaseAction
{

    public function __construct(private readonly GetAllFilmFestivalsUseCase $useCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $response = $this->useCase->execute();

        return new Response(json_encode($response->filmFestivalList));
    }

}
