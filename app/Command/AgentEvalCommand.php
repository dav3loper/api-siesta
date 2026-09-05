<?php

namespace Siesta\App\Command;

use Siesta\Agent\Application\Chat\ChatWithAgentRequest;
use Siesta\Agent\Application\Chat\ChatWithAgentUseCase;
use Siesta\Agent\Domain\Stream\TextChunk;
use Siesta\Agent\Domain\Stream\ToolInvoked;
use Siesta\Agent\Domain\Stream\UnknownTitlesDetected;
use Siesta\Agent\Domain\UserMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs the agent against a fixed set of cases with deterministic assertions, so a change
 * in the prompt or the model can be compared against the previous run. It calls the real
 * API, so every run costs money and is kept out of the PHPUnit suite on purpose.
 */
class AgentEvalCommand extends Command
{
    public function __construct(private readonly ChatWithAgentUseCase $chatWithAgentUseCase)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('agent:eval');
        $this->setDescription('Runs the recommendation agent against the eval cases and reports how many pass')
            ->addOption('cases', null, InputOption::VALUE_REQUIRED, 'Path to the cases file', 'evals/cases.json')
            ->addOption('threshold', null, InputOption::VALUE_REQUIRED, 'Minimum percentage of passing cases', '80')
            ->addOption('output-dir', null, InputOption::VALUE_REQUIRED, 'Where to store the run report', 'var/evals');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $casesFile = $input->getOption('cases');
        if (!is_file($casesFile)) {
            $output->writeln("<error>No encuentro el fichero de casos: {$casesFile}</error>");
            return Command::FAILURE;
        }

        $suite = json_decode(file_get_contents($casesFile), true);
        $userId = (string)$suite['user_id'];

        $results = [];
        foreach ($suite['cases'] as $case) {
            $results[] = $this->runCase($case, $userId, $output);
        }

        $passed = count(array_filter($results, fn (array $result): bool => $result['failures'] === []));
        $total = count($results);
        $percentage = $total === 0 ? 0.0 : round($passed * 100 / $total, 1);

        $this->render($results, $output);
        $reportFile = $this->storeReport($results, $percentage, $input->getOption('output-dir'));

        $output->writeln('');
        $output->writeln("Pasan {$passed}/{$total} casos ({$percentage}%). Informe: {$reportFile}");

        return $percentage >= (float)$input->getOption('threshold') ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @param array<string, mixed> $case
     *
     * @return array<string, mixed>
     */
    private function runCase(array $case, string $userId, OutputInterface $output): array
    {
        $output->writeln("Ejecutando <info>{$case['id']}</info>...");

        $context = $case['context'] ?? [];
        $request = new ChatWithAgentRequest(
            'eval-' . $case['id'],
            $userId,
            null,
            new UserMessage($case['message']),
            $context['movie_title'] ?? null,
            $context['movie_year'] ?? null,
        );

        $answer = '';
        $unknownTitles = [];
        $usedTools = [];
        foreach ($this->chatWithAgentUseCase->execute($request) as $event) {
            if ($event instanceof TextChunk) {
                $answer .= $event->text;
            }
            if ($event instanceof UnknownTitlesDetected) {
                $unknownTitles = $event->titles;
            }
            if ($event instanceof ToolInvoked) {
                $usedTools[] = $event->toolName;
            }
        }

        $usedTools = array_values(array_unique($usedTools));

        return [
            'id' => $case['id'],
            'answer' => $answer,
            'unknown_titles' => $unknownTitles,
            'used_tools' => $usedTools,
            'failures' => $this->assert($case, $answer, $unknownTitles, $usedTools),
        ];
    }

    /**
     * @param array<string, mixed> $case
     * @param string[] $unknownTitles
     * @param string[] $usedTools
     *
     * @return string[]
     */
    private function assert(array $case, string $answer, array $unknownTitles, array $usedTools): array
    {
        $failures = [];

        foreach ($case['must_use_tools'] ?? [] as $tool) {
            if (!in_array($tool, $usedTools, true)) {
                $failures[] = "no usa la herramienta {$tool}";
            }
        }

        foreach ($case['must_contain'] ?? [] as $needle) {
            if (mb_stripos($answer, $needle) === false) {
                $failures[] = "no menciona \"{$needle}\"";
            }
        }

        foreach ($case['must_not_contain'] ?? [] as $needle) {
            if (mb_stripos($answer, $needle) !== false) {
                $failures[] = "menciona \"{$needle}\" y no debería";
            }
        }

        if (isset($case['max_length']) && mb_strlen($answer) > $case['max_length']) {
            $failures[] = 'respuesta demasiado larga (' . mb_strlen($answer) . " > {$case['max_length']})";
        }

        if ($unknownTitles !== []) {
            $failures[] = 'títulos que no están en el catálogo: ' . implode(', ', $unknownTitles);
        }

        return $failures;
    }

    /**
     * @param array<int, array<string, mixed>> $results
     */
    private function render(array $results, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders(['Caso', 'Resultado', 'Motivo']);

        foreach ($results as $result) {
            $table->addRow([
                $result['id'],
                $result['failures'] === [] ? '<info>OK</info>' : '<error>KO</error>',
                implode('; ', $result['failures']),
            ]);
        }

        $table->render();
    }

    /**
     * @param array<int, array<string, mixed>> $results
     */
    private function storeReport(array $results, float $percentage, string $outputDir): string
    {
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $reportFile = $outputDir . '/' . date('Ymd-His') . '.json';
        file_put_contents(
            $reportFile,
            json_encode(
                ['percentage' => $percentage, 'results' => $results],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );

        return $reportFile;
    }
}
