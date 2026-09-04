<?php

namespace Siesta\Tests\Agent\Application\Chat;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Application\Chat\ChatWithAgentRequest;
use Siesta\Agent\Application\Chat\ChatWithAgentUseCase;
use Siesta\Agent\Domain\AgentClient;
use Siesta\Agent\Domain\RatedMovie;
use Siesta\Agent\Domain\RatedMovieCollection;
use Siesta\Agent\Domain\UserProfile;
use Siesta\Agent\Domain\UserProfileRepository;
use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;

class ChatWithAgentUseCaseTest extends TestCase
{
    private ChatWithAgentUseCase $useCase;
    /** @var UserProfileRepository&MockObject */
    private mixed $userProfileRepository;
    /** @var AgentClient&MockObject */
    private mixed $agentClient;

    public function setUp(): void
    {
        $this->userProfileRepository = $this->createMock(UserProfileRepository::class);
        $this->agentClient = $this->createMock(AgentClient::class);
        $this->useCase = new ChatWithAgentUseCase($this->userProfileRepository, $this->agentClient, true);
    }

    #[Test]
    public function whenChattingThenStreamsAnswerBuiltFromUserProfile(): void
    {
        $profile = new UserProfile(new RatedMovieCollection([
            new RatedMovie('Alien', Score::LOVED, 5),
            new RatedMovie('Titane', null, 2),
        ]));

        $this->userProfileRepository->expects(self::once())
            ->method('getByUserId')
            ->with(new Id('1'))
            ->willReturn($profile);

        $this->agentClient->expects(self::once())
            ->method('streamAnswer')
            ->with(
                self::callback(function (string $systemPrompt): bool {
                    return str_contains($systemPrompt, 'Alien')
                        && str_contains($systemPrompt, 'voto: LOVED')
                        && str_contains($systemPrompt, 'Titane')
                        && str_contains($systemPrompt, 'rating: 2/5');
                }),
                '¿qué me recomiendas?'
            )
            ->willReturn(['Te', ' recomiendo', ' Alien']);

        $request = new ChatWithAgentRequest('1', '¿qué me recomiendas?', null, null);
        $chunks = iterator_to_array($this->useCase->execute($request));

        self::assertEquals(['Te', ' recomiendo', ' Alien'], $chunks);
    }

    #[Test]
    public function whenMovieContextIsGivenThenIncludesItInSystemPrompt(): void
    {
        $this->userProfileRepository->expects(self::once())
            ->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));

        $this->agentClient->expects(self::once())
            ->method('streamAnswer')
            ->with(
                self::callback(function (string $systemPrompt): bool {
                    return str_contains($systemPrompt, 'Late Night with the Devil')
                        && str_contains($systemPrompt, '(2026)');
                }),
                'qué me puedes contar de esta película?'
            )
            ->willReturn(['Va sobre...']);

        $request = new ChatWithAgentRequest('1', 'qué me puedes contar de esta película?', 'Late Night with the Devil', 2026);
        iterator_to_array($this->useCase->execute($request));
    }

    #[Test]
    public function whenUserHasNoHistoryThenSystemPromptSaysSo(): void
    {
        $profile = new UserProfile(new RatedMovieCollection([]));

        $this->userProfileRepository->expects(self::once())
            ->method('getByUserId')
            ->willReturn($profile);

        $this->agentClient->expects(self::once())
            ->method('streamAnswer')
            ->with(
                self::callback(fn (string $systemPrompt): bool => str_contains($systemPrompt, 'Sin historial todavía.')),
                self::anything()
            )
            ->willReturn([]);

        iterator_to_array($this->useCase->execute(new ChatWithAgentRequest('1', 'hola', null, null)));
    }

    #[Test]
    public function whenCommunicationsAreDisabledThenReturnsFixedMessageWithoutCallingAgent(): void
    {
        $useCase = new ChatWithAgentUseCase($this->userProfileRepository, $this->agentClient, false);

        $this->userProfileRepository->expects(self::never())->method('getByUserId');
        $this->agentClient->expects(self::never())->method('streamAnswer');

        $chunks = iterator_to_array($useCase->execute(new ChatWithAgentRequest('1', 'hola', null, null)));

        self::assertEquals(['Las comunicaciones están apagadas por ahora...'], $chunks);
    }
}
