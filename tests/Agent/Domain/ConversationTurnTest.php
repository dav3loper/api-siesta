<?php

namespace Siesta\Tests\Agent\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Domain\ConversationTurn;
use Siesta\Agent\Domain\UserMessage;

class ConversationTurnTest extends TestCase
{
    #[Test]
    public function whenTheTurnHadAMovieSheetOpenThenTheMessageCarriesIt(): void
    {
        $turn = new ConversationTurn(new UserMessage('¿me va a gustar?'), 'Puede que sí', 'Titane', 2021);

        self::assertEquals('[ficha abierta: Titane (2021)] ¿me va a gustar?', $turn->messageWithContext());
    }

    #[Test]
    public function whenTheTurnHadNoMovieSheetOpenThenTheMessageIsUnchanged(): void
    {
        $turn = new ConversationTurn(new UserMessage('¿qué me recomiendas?'), 'Mira «Titane»', null, null);

        self::assertEquals('¿qué me recomiendas?', $turn->messageWithContext());
    }

    #[Test]
    public function whenTheMovieHasNoYearThenOnlyTheTitleIsAdded(): void
    {
        $turn = new ConversationTurn(new UserMessage('¿de qué va?'), 'De un coche', 'Titane', null);

        self::assertEquals('[ficha abierta: Titane] ¿de qué va?', $turn->messageWithContext());
    }
}
