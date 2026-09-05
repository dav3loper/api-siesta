<?php

namespace Siesta\Tests\Agent\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Domain\UserMessage;
use Siesta\Shared\Exception\ValueNotValid;

class UserMessageTest extends TestCase
{
    #[Test]
    public function whenMessageHasContentThenItIsTrimmed(): void
    {
        self::assertEquals('¿qué me recomiendas?', (new UserMessage("  ¿qué me recomiendas?\n"))->value());
    }

    #[Test]
    public function whenMessageIsEmptyThenThrowsValueNotValid(): void
    {
        $this->expectException(ValueNotValid::class);

        new UserMessage('   ');
    }

    #[Test]
    public function whenMessageIsTooLongThenThrowsValueNotValid(): void
    {
        $this->expectException(ValueNotValid::class);

        new UserMessage(str_repeat('a', 1001));
    }

    #[Test]
    public function whenMessageIsAtTheLengthLimitThenItIsAccepted(): void
    {
        self::assertEquals(1000, mb_strlen((new UserMessage(str_repeat('a', 1000)))->value()));
    }
}
