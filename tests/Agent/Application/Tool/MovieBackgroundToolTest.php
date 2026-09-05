<?php

namespace Siesta\Tests\Agent\Application\Tool;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Application\Tool\MovieBackgroundTool;
use Siesta\Agent\Domain\Background\MovieBackground;
use Siesta\Agent\Domain\Background\MovieBackgroundFinder;
use Siesta\Agent\Domain\Background\MovieBackgroundRepository;

class MovieBackgroundToolTest extends TestCase
{
    private MovieBackgroundTool $tool;
    /** @var MovieBackgroundRepository&MockObject */
    private mixed $movieBackgroundRepository;
    /** @var MovieBackgroundFinder&MockObject */
    private mixed $movieBackgroundFinder;

    public function setUp(): void
    {
        $this->movieBackgroundRepository = $this->createMock(MovieBackgroundRepository::class);
        $this->movieBackgroundFinder = $this->createMock(MovieBackgroundFinder::class);
        $this->tool = new MovieBackgroundTool($this->movieBackgroundRepository, $this->movieBackgroundFinder);
    }

    #[Test]
    public function whenTheMovieIsNotCachedYetThenLooksItUpAndStoresIt(): void
    {
        $this->movieBackgroundRepository->expects(self::once())
            ->method('findBySearchedTitle')
            ->with('Titane')
            ->willReturn(null);

        $this->movieBackgroundFinder->expects(self::once())
            ->method('findByTitle')
            ->with('Titane', 2021)
            ->willReturn($this->titane());

        $this->movieBackgroundRepository->expects(self::once())
            ->method('save')
            ->with('Titane', self::isInstanceOf(MovieBackground::class));

        $result = $this->tool->execute(['title' => 'Titane', 'year' => 2021]);

        self::assertStringContainsString('Julia Ducournau', $result);
        self::assertStringContainsString('Crudo (2016)', $result);
        self::assertStringContainsString('Agathe Rousselle', $result);
        self::assertStringContainsString('Terror', $result);
        self::assertStringContainsString('Ganador: Palma de Oro', $result);
        self::assertStringContainsString('7/10 (4213 votos)', $result);
    }

    #[Test]
    public function whenTheMovieIsAlreadyCachedThenDoesNotAskTheExternalSourceAgain(): void
    {
        $this->movieBackgroundRepository->expects(self::once())
            ->method('findBySearchedTitle')
            ->with('Titane')
            ->willReturn($this->titane());

        $this->movieBackgroundFinder->expects(self::never())->method('findByTitle');
        $this->movieBackgroundRepository->expects(self::never())->method('save');

        self::assertStringContainsString('Julia Ducournau', $this->tool->execute(['title' => 'Titane']));
    }

    #[Test]
    public function whenTheExternalSourceKnowsNothingThenSaysSoWithoutCachingAnything(): void
    {
        $this->movieBackgroundRepository->method('findBySearchedTitle')->willReturn(null);
        $this->movieBackgroundFinder->expects(self::once())
            ->method('findByTitle')
            ->with('Estreno Inédito', null)
            ->willReturn(null);
        $this->movieBackgroundRepository->expects(self::never())->method('save');

        self::assertStringContainsString(
            'No hay información externa',
            $this->tool->execute(['title' => 'Estreno Inédito'])
        );
    }

    #[Test]
    public function whenNoTitleIsGivenThenAsksForOneWithoutLookingAnythingUp(): void
    {
        $this->movieBackgroundRepository->expects(self::never())->method('findBySearchedTitle');
        $this->movieBackgroundFinder->expects(self::never())->method('findByTitle');

        self::assertStringContainsString('Indica el título', $this->tool->execute([]));
    }

    #[Test]
    public function whenTheSourceOnlyKnowsTheTitleThenOmitsTheMissingFields(): void
    {
        $this->movieBackgroundRepository->method('findBySearchedTitle')
            ->willReturn(new MovieBackground('Estreno Inédito', null, null, [], [], [], [], null, 0));

        $result = $this->tool->execute(['title' => 'Estreno Inédito']);

        self::assertEquals('Estreno Inédito', $result);
    }

    private function titane(): MovieBackground
    {
        return new MovieBackground(
            'Titane',
            2021,
            'Julia Ducournau',
            ['Terror', 'Drama'],
            ['Agathe Rousselle', 'Vincent Lindon'],
            ['Crudo (2016)'],
            ['Ganador: Palma de Oro (Festival de Cannes 2021)'],
            7.0,
            4213,
        );
    }
}
