<?php

namespace Siesta\App\Command;

use Siesta\Extraction\Domain\FinderVideoService;
use Siesta\Extraction\Domain\Movie;
use Siesta\Extraction\Domain\MovieListFinder;
use Siesta\Extraction\Domain\MovieRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ObtainMoviesFromLetterboxdCommand extends Command
{
    private const NO_TRAILER = 'notrailer';

    public function __construct(
        private readonly MovieListFinder $movieListFinder,
        private readonly MovieRepository  $movieRepository,
        private readonly FinderVideoService $finderVideoService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('obtain-movies-from-letterboxd');
        $this->setDescription('Seed movies from a Letterboxd list (title + trailer only; duration/summary/sessions are left empty and get filled in by re-running obtain-movies with the official JSON once it is available)')
            ->addArgument('url', InputArgument::REQUIRED, 'Letterboxd list URL, e.g. https://letterboxd.com/user/list/sitges-2026/')
            ->addArgument('edition_id', InputArgument::REQUIRED, 'Edition to import')
            ->addOption('stop-on-first-trailer-failure', null, InputOption::VALUE_NONE, 'Abort without importing anything if the trailer search fails for the first movie (useful to catch a broken YouTube API key/quota early)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $editionId = (int)$input->getArgument('edition_id');
        $stopOnFirstTrailerFailure = (bool)$input->getOption('stop-on-first-trailer-failure');
        $entries = $this->movieListFinder->findAll($input->getArgument('url'));

        foreach ($entries as $index => $entry) {
            $trailer = $this->finderVideoService->findByText("$entry->title official trailer");
            if ($index === 0 && $stopOnFirstTrailerFailure && $trailer === self::NO_TRAILER) {
                $output->writeln("Trailer search failed for the first movie ($entry->title), aborting");
                return self::FAILURE;
            }
            $this->movieRepository->store(new Movie(
                $entry->title,
                '',
                $trailer,
                0,
                '',
                $entry->link,
                $editionId,
                '',
                [],
            ));
            $output->writeln($entry->title);
        }

        $output->writeln(sprintf('Imported %d movies from Letterboxd', count($entries)));

        return self::SUCCESS;
    }
}
