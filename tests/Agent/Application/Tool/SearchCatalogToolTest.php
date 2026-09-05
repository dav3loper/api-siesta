<?php

namespace Siesta\Tests\Agent\Application\Tool;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Application\Tool\SearchCatalogTool;
use Siesta\Agent\Domain\CatalogMovie;
use Siesta\Agent\Domain\CatalogMovieCollection;
use Siesta\Agent\Domain\MovieCatalog;

class SearchCatalogToolTest extends TestCase
{
    private SearchCatalogTool $tool;
    /** @var MovieCatalog&MockObject */
    private mixed $movieCatalog;

    public function setUp(): void
    {
        $this->movieCatalog = $this->createMock(MovieCatalog::class);
        $this->tool = new SearchCatalogTool($this->movieCatalog);
    }

    #[Test]
    public function whenSearchingByTitleThenReturnsTheMatchingMovies(): void
    {
        $this->movieCatalog->expects(self::once())
            ->method('searchByTitle')
            ->with('tita')
            ->willReturn(new CatalogMovieCollection([
                new CatalogMovie('Titane', 'Oficial Fantàstic', 108, 'Una historia de metal.'),
            ]));

        $result = $this->tool->execute(['title' => 'tita']);

        self::assertStringContainsString('Titane', $result);
        self::assertStringContainsString('Oficial Fantàstic', $result);
        self::assertStringContainsString('108 min', $result);
    }

    #[Test]
    public function whenNothingMatchesThenSaysSoInsteadOfReturningNothing(): void
    {
        $this->movieCatalog->expects(self::once())
            ->method('searchByTitle')
            ->willReturn(new CatalogMovieCollection([]));

        self::assertStringContainsString('Ninguna película', $this->tool->execute(['title' => 'inexistente']));
    }

    #[Test]
    public function whenSearchingBySectionThenUsesTheSectionSearch(): void
    {
        $this->movieCatalog->expects(self::never())->method('searchByTitle');
        $this->movieCatalog->expects(self::once())
            ->method('searchBySection')
            ->with('Midnight X-Treme')
            ->willReturn(new CatalogMovieCollection([new CatalogMovie('Terrifier', 'Midnight X-Treme', 90, null)]));

        self::assertStringContainsString('Terrifier', $this->tool->execute(['section' => 'Midnight X-Treme']));
    }

    #[Test]
    public function whenNoCriteriaIsGivenThenAsksForOneWithoutQueryingTheCatalog(): void
    {
        $this->movieCatalog->expects(self::never())->method('searchByTitle');
        $this->movieCatalog->expects(self::never())->method('searchBySection');

        self::assertStringContainsString('Indica', $this->tool->execute([]));
    }
}
