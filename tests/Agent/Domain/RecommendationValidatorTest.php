<?php

namespace Siesta\Tests\Agent\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Domain\MovieCatalog;
use Siesta\Agent\Domain\RecommendationValidator;

class RecommendationValidatorTest extends TestCase
{
    private RecommendationValidator $validator;
    /** @var MovieCatalog&MockObject */
    private mixed $movieCatalog;

    public function setUp(): void
    {
        $this->movieCatalog = $this->createMock(MovieCatalog::class);
        $this->validator = new RecommendationValidator($this->movieCatalog);
    }

    #[Test]
    public function whenEveryCitedTitleIsInTheCatalogThenThereIsNothingUnknown(): void
    {
        $this->movieCatalog->expects(self::once())
            ->method('existingTitles')
            ->with(['Titane', 'Alien'])
            ->willReturn(['Titane', 'Alien']);

        self::assertEquals([], $this->validator->unknownTitles('Te gustará «Titane», y también «Alien».'));
    }

    #[Test]
    public function whenACitedTitleIsNotInTheCatalogThenItIsReported(): void
    {
        $this->movieCatalog->expects(self::once())
            ->method('existingTitles')
            ->willReturn(['Titane']);

        self::assertEquals(
            ['El zapatero galáctico'],
            $this->validator->unknownTitles('Mira «Titane» o «El zapatero galáctico».')
        );
    }

    #[Test]
    public function whenTheCatalogUsesADifferentCasingThenTheTitleIsStillKnown(): void
    {
        $this->movieCatalog->expects(self::once())
            ->method('existingTitles')
            ->willReturn(['TITANE']);

        self::assertEquals([], $this->validator->unknownTitles('Mira «Titane».'));
    }

    #[Test]
    public function whenNoTitleIsCitedThenTheCatalogIsNotQueried(): void
    {
        $this->movieCatalog->expects(self::never())->method('existingTitles');

        self::assertEquals([], $this->validator->unknownTitles('No me queda claro qué buscas.'));
    }
}
