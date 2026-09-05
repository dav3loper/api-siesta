<?php

namespace Siesta\Tests\Agent\Application\Chat;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Siesta\Agent\Application\Chat\ChatWithAgentRequest;
use Siesta\Agent\Application\Chat\ChatWithAgentUseCase;
use Siesta\Agent\Application\Tool\MovieBackgroundTool;
use Siesta\Agent\Application\Tool\SearchCatalogTool;
use Siesta\Agent\Domain\AgentClient;
use Siesta\Agent\Domain\Background\MovieBackgroundFinder;
use Siesta\Agent\Domain\Background\MovieBackgroundRepository;
use Siesta\Agent\Domain\ConversationTurn;
use Siesta\Agent\Domain\ConversationTurnCollection;
use Siesta\Agent\Domain\Interaction\InteractionStatus;
use Siesta\Agent\Domain\MovieCatalog;
use Siesta\Agent\Domain\RatedMovie;
use Siesta\Agent\Domain\RatedMovieCollection;
use Siesta\Agent\Domain\RateLimitExceeded;
use Siesta\Agent\Domain\RecommendationValidator;
use Siesta\Agent\Domain\Stream\TextChunk;
use Siesta\Agent\Domain\Stream\ToolInvoked;
use Siesta\Agent\Domain\Stream\UnknownTitlesDetected;
use Siesta\Agent\Domain\Tool\AgentToolCollection;
use Siesta\Agent\Domain\UserMessage;
use Siesta\Agent\Domain\UserProfile;
use Siesta\Agent\Domain\UserProfileRepository;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;
use Siesta\Tests\Fixtures\Agent\InMemoryAgentInteractionRepository;

class ChatWithAgentUseCaseTest extends TestCase
{
    private ChatWithAgentUseCase $useCase;
    /** @var UserProfileRepository&MockObject */
    private mixed $userProfileRepository;
    /** @var AgentClient&MockObject */
    private mixed $agentClient;
    /** @var RecommendationValidator&MockObject */
    private mixed $recommendationValidator;
    private InMemoryAgentInteractionRepository $agentInteractionRepository;

    public function setUp(): void
    {
        $this->userProfileRepository = $this->createMock(UserProfileRepository::class);
        $this->agentClient = $this->createMock(AgentClient::class);
        $this->recommendationValidator = $this->createMock(RecommendationValidator::class);
        $this->agentInteractionRepository = new InMemoryAgentInteractionRepository();

        $this->useCase = $this->useCaseWithCommunications(true);
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
                self::anything(),
                self::callback(fn (UserMessage $message): bool => $message->value() === '¿qué me recomiendas?'),
                self::anything()
            )
            ->willReturn([new TextChunk('Te'), new TextChunk(' recomiendo'), new TextChunk(' Alien')]);

        $chunks = $this->textOf($this->useCase->execute($this->request('¿qué me recomiendas?', null, null)));

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
                self::anything(),
                self::anything(),
                self::anything()
            )
            ->willReturn([new TextChunk('Va sobre...')]);

        $request = $this->request('qué me puedes contar de esta película?', 'Late Night with the Devil', 2026);
        $this->textOf($this->useCase->execute($request));
    }

    #[Test]
    public function whenUserHasNoHistoryThenSystemPromptSaysSo(): void
    {
        $this->userProfileRepository->expects(self::once())
            ->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));

        $this->agentClient->expects(self::once())
            ->method('streamAnswer')
            ->with(
                self::callback(fn (string $systemPrompt): bool => str_contains($systemPrompt, 'Sin historial todavía.')),
                self::anything(),
                self::anything(),
                self::anything()
            )
            ->willReturn([]);

        $this->textOf($this->useCase->execute($this->request('hola', null, null)));
    }

    #[Test]
    public function whenCommunicationsAreDisabledThenReturnsFixedMessageWithoutCallingAgent(): void
    {
        $useCase = $this->useCaseWithCommunications(false);

        $this->userProfileRepository->expects(self::never())->method('getByUserId');
        $this->agentClient->expects(self::never())->method('streamAnswer');

        $chunks = $this->textOf($useCase->execute($this->request('hola', null, null)));

        self::assertEquals(['Las comunicaciones están apagadas por ahora...'], $chunks);
        self::assertEquals([], $this->agentInteractionRepository->saved);
    }

    #[Test]
    public function whenChattingThenRecordsTheInteractionBeforeAndAfterAnswering(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentClient->method('streamAnswer')
            ->willReturn([new TextChunk('Te recomiendo '), new TextChunk('«Titane»')]);

        $this->textOf($this->useCase->execute($this->request('¿qué veo?', 'Titane', 2021)));

        self::assertEquals(
            [InteractionStatus::STARTED, InteractionStatus::COMPLETED],
            $this->agentInteractionRepository->savedStatuses
        );

        $interaction = $this->agentInteractionRepository->last();
        self::assertEquals('Te recomiendo «Titane»', $interaction->response());
        self::assertEquals('¿qué veo?', $interaction->message->value());
        self::assertEquals('Titane', $interaction->movieTitle);
        self::assertEquals('claude-sonnet-5', $interaction->model);
    }

    #[Test]
    public function whenTheAgentFailsMidStreamThenTheInteractionIsRecordedAsFailed(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentClient->method('streamAnswer')
            ->willThrowException(new InternalError('la API no responde'));

        try {
            $this->textOf($this->useCase->execute($this->request('hola', null, null)));
            self::fail('The failure of the agent should have been propagated');
        } catch (InternalError) {
        }

        self::assertEquals(InteractionStatus::FAILED, $this->agentInteractionRepository->last()->status());
        self::assertEquals('la API no responde', $this->agentInteractionRepository->last()->error());
    }

    #[Test]
    public function whenTheProfileCannotBeLoadedThenItFailsBeforeStreamingAnything(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willThrowException(new InternalError('la base de datos no responde'));

        $this->expectException(InternalError::class);

        // No iteration here on purpose: the failure must surface while the response can still be a 500.
        $this->useCase->execute($this->request('hola', null, null));
    }

    #[Test]
    public function whenTheRateLimitIsExceededThenRejectsWithoutCallingTheAgent(): void
    {
        $this->agentInteractionRepository->interactionsInWindow = 30;
        $this->userProfileRepository->expects(self::never())->method('getByUserId');
        $this->agentClient->expects(self::never())->method('streamAnswer');

        $this->expectException(RateLimitExceeded::class);

        try {
            $this->useCase->execute($this->request('hola', null, null));
        } finally {
            self::assertEquals([InteractionStatus::REJECTED], $this->agentInteractionRepository->savedStatuses);
        }
    }

    #[Test]
    public function whenTheAgentUsesToolsThenTheyAreRecordedButNotStreamedAsText(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentClient->method('streamAnswer')->willReturn([
            new TextChunk('Déjame mirar. '),
            new ToolInvoked('search_catalog', ['title' => 'Titane'], '- Titane'),
            new TextChunk('Sí, está.'),
        ]);

        $events = iterator_to_array($this->useCase->execute($this->request('¿está Titane?', null, null)));

        self::assertEquals(['Déjame mirar. ', 'Sí, está.'], $this->textOf($events));
        self::assertCount(1, $this->agentInteractionRepository->last()->toolCalls());
    }

    #[Test]
    public function whenTheAgentUsesToolsThenTheyAreForwardedSoTheCallerCanObserveThem(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentClient->method('streamAnswer')->willReturn([
            new ToolInvoked('movie_background', ['title' => 'Titane'], 'Dirigida por: Julia Ducournau'),
            new TextChunk('La dirige Julia Ducournau.'),
        ]);

        $events = iterator_to_array($this->useCase->execute($this->request('¿quién la dirige?', 'Titane', 2021)));

        $toolEvents = array_values(array_filter($events, fn ($event): bool => $event instanceof ToolInvoked));
        self::assertCount(1, $toolEvents);
        self::assertEquals('movie_background', $toolEvents[0]->toolName);
    }

    #[Test]
    public function shouldOfferTheCatalogAndTheBackgroundToolsToTheAgent(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));

        $this->agentClient->expects(self::once())
            ->method('streamAnswer')
            ->with(
                self::anything(),
                self::anything(),
                self::anything(),
                self::callback(function (AgentToolCollection $tools): bool {
                    return $tools->findByName('search_catalog') !== null
                        && $tools->findByName('movie_background') !== null;
                })
            )
            ->willReturn([]);

        $this->textOf($this->useCase->execute($this->request('¿quién dirige esto?', 'Titane', 2021)));
    }

    #[Test]
    public function whenTheAgentCitesAMovieOutOfTheCatalogThenItIsReportedAtTheEnd(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentClient->method('streamAnswer')->willReturn([new TextChunk('Mira «Inventada».')]);
        $this->recommendationValidator->expects(self::once())
            ->method('unknownTitles')
            ->with('Mira «Inventada».')
            ->willReturn(['Inventada']);

        $events = iterator_to_array($this->useCase->execute($this->request('¿qué veo?', null, null)));

        self::assertInstanceOf(UnknownTitlesDetected::class, $events[count($events) - 1]);
        self::assertEquals(['Inventada'], $this->agentInteractionRepository->last()->unknownTitles());
    }

    #[Test]
    public function whenTheConversationHasPreviousTurnsThenTheyAreSentToTheAgent(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentInteractionRepository->history = new ConversationTurnCollection([
            new ConversationTurn(new UserMessage('¿qué me recomiendas?'), 'Mira «Titane»', null, null),
        ]);

        $this->agentClient->expects(self::once())
            ->method('streamAnswer')
            ->with(
                self::anything(),
                self::callback(function (ConversationTurnCollection $history): bool {
                    $turn = $history->items()[0];

                    return $history->count() === 1
                        && $turn->userMessage->value() === '¿qué me recomiendas?'
                        && $turn->agentResponse === 'Mira «Titane»';
                }),
                self::anything(),
                self::anything()
            )
            ->willReturn([new TextChunk('Dura 108 minutos.')]);

        $this->textOf($this->useCase->execute($this->request('¿cuánto dura?', null, null)));
    }

    #[Test]
    public function shouldAskForTheHistoryOfThatConversationAndThatUserOnly(): void
    {
        $this->userProfileRepository->method('getByUserId')
            ->willReturn(new UserProfile(new RatedMovieCollection([])));
        $this->agentClient->method('streamAnswer')->willReturn([]);

        $this->textOf($this->useCase->execute($this->request('hola', null, null)));

        self::assertEquals(
            ['userId' => '1', 'conversationId' => 'conversation-1', 'maxTurns' => 10],
            $this->agentInteractionRepository->historyQuery
        );
    }

    private function useCaseWithCommunications(bool $communicationsEnabled): ChatWithAgentUseCase
    {
        return new ChatWithAgentUseCase(
            $this->userProfileRepository,
            $this->agentInteractionRepository,
            new SearchCatalogTool($this->createMock(MovieCatalog::class)),
            new MovieBackgroundTool(
                $this->createMock(MovieBackgroundRepository::class),
                $this->createMock(MovieBackgroundFinder::class)
            ),
            $this->recommendationValidator,
            $this->agentClient,
            $communicationsEnabled,
            'claude-sonnet-5',
        );
    }

    private function request(string $message, ?string $movieTitle, ?int $movieYear): ChatWithAgentRequest
    {
        return new ChatWithAgentRequest(
            'conversation-1',
            '1',
            '7',
            new UserMessage($message),
            $movieTitle,
            $movieYear
        );
    }

    /**
     * @param iterable<mixed> $events
     *
     * @return string[]
     */
    private function textOf(iterable $events): array
    {
        $chunks = [];
        foreach ($events as $event) {
            if ($event instanceof TextChunk) {
                $chunks[] = $event->text;
            }
        }

        return $chunks;
    }
}
