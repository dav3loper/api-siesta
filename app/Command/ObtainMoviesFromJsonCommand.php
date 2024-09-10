<?php

namespace Siesta\App\Command;

use Siesta\Extraction\Domain\FinderVideoService;
use Siesta\Extraction\Domain\Movie;
use Siesta\Extraction\Domain\MovieRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ObtainMoviesFromJsonCommand extends Command
{

    private const HOST = 'https://sitgesfilmfestival.com/';

    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly FinderVideoService $finderVideoService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('obtain-movies');
        $this->setDescription('Import movies from a location: file or web')
            ->addArgument('file', InputArgument::REQUIRED, 'File to import')
            ->addArgument('edition_id', InputArgument::REQUIRED, 'Edition to import');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $editionId = $input->getArgument('edition_id');
        $data = file_get_contents($input->getArgument('file'));
        $jsonData = json_decode($data, true);
        $movieList = [];
        foreach($jsonData['films'] as $movieData){
            if($movieData['types'] && $movieData['types'][0] !== '611-film_types'){
                continue;
            }
            $movieList[] = new Movie(
                $movieData['international_title'],
                $movieData['image'],
                $this->getTrailer($movieData['original_title']),
                $movieData['duration'],
                $movieData['synopsis']['es'],
                self::HOST.$movieData['url']['es'],
                $editionId
            );
        }
        array_map(fn(Movie $movie) => $this->movieRepository->store($movie), $movieList);

        return self::SUCCESS;
    }

    private function getTrailer(string $title): string
    {
        try {
            $result = $this->finderVideoService->findByText("lord of the rings official trailer");
            return $result;
        }catch (\Throwable $e){
            echo $e->getMessage();
        }

    }
}