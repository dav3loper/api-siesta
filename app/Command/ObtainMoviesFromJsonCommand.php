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
        private readonly MovieTransformer $movieTransformer,
    )
    {
        parent::__construct();
    }

    public function extractSessions(false|string $sessionData): array
    {
        $jsonSessionData = json_decode($sessionData, true)['sessions'];
        $sessionsById = [];
        foreach ($jsonSessionData as $session) {
            $sessionsById[$session['name']['es']][] = [
                'location' => $this->movieTransformer->fromDataLocationToLocation($session['locations'][0]),
                'init_date' => $session['start_date'],
                'end_date' => $session['end_date'],
            ];
        }
        return $sessionsById;
    }

    protected function configure(): void
    {
        $this->setName('obtain-movies');
        $this->setDescription('Import movies from a location: file or web')
            ->addArgument('file', InputArgument::REQUIRED, 'Movies to import')
            ->addArgument('sessions', InputArgument::REQUIRED, 'Sessions to import')
            ->addArgument('edition_id', InputArgument::REQUIRED, 'Edition to import');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $editionId = $input->getArgument('edition_id');
        exit;
        $data = file_get_contents($input->getArgument('file'));
        $sessionData = file_get_contents($input->getArgument('sessions'));
        $jsonData = json_decode($data, true);
        $sessionsById = $this->extractSessions($sessionData);

        $movieList = [];
        foreach($jsonData['films'] as $movieData){
            if($movieData['types'] && $movieData['types'][0] !== '611-film_types'){
                continue;
            }
            $titleForTrailer = !empty($movieData['original_title'])
                ? $movieData['original_title']
                : $movieData['international_title'];

            $movieList[] = new Movie(
                $movieData['international_title'],
                $movieData['image'],
                $this->getTrailer($titleForTrailer),
                $movieData['duration'],
                $movieData['synopsis']['es'],
                self::HOST.$movieData['url']['es'],
                $editionId,
                $this->movieTransformer->fromCategoryDataToCategory($movieData['sections'][0] ?? ''),
                $sessionsById[$movieData['international_title']] ?? []
            );
        }
        array_map(fn(Movie $movie) => $this->movieRepository->store($movie), $movieList);

        return self::SUCCESS;
    }

    private function getTrailer(string $title): string
    {
        try {
            $result = $this->finderVideoService->findByText("$title official trailer");
            return $result;
        }catch (\Throwable $e){
            echo $e->getMessage();
            return 'notrailer';
        }

    }
}