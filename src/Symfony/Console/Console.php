<?php

declare(strict_types=1);

namespace Xgc\Symfony\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentInterface;
use Xgc\Exception\BaseException;
use Xgc\Message\BusInterface;
use Xgc\Message\Command;
use Xgc\Message\Query;
use Xgc\Message\RetryStamp;
use Xgc\Utils\ContainerBoxTrait;
use Xgc\Utils\StringUtil;

use function count;
use function in_array;

use const PHP_EOL;

abstract class Console extends BaseCommand
{
    use ContainerBoxTrait;

    private ?InputInterface $input;

    private ?OutputInterface $output;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->input = null;
        $this->output = null;
    }

    /**
     * @param string[]|null $validResponses
     * @param string[] $autocomplete
     * @param callable(string): bool|null $cb
     */
    protected function ask(
        string $question,
        ?array $validResponses = null,
        ?string $default = null,
        array $autocomplete = [],
        ?callable $cb = null
    ): string {
        $pre = '';
        if (count($validResponses ?? []) > 0) {
            $group = implode(',', $validResponses);
            $pre = " [{$group}]";
            if ($default !== '') {
                $pre .= "({$default}) ";
            }
        } elseif ($default !== '' && $default !== null) {
            $pre .= "({$default}) ";
        } else {
            $pre = ' ';
        }

        $repeatQuestion = true;
        do {
            if ($repeatQuestion && $question !== '') {
                $this->output()->write($question . PHP_EOL . ' <white2>></white2>' . $pre);
            } else {
                $this->output()->write(' <white2>></white2>' . $pre);
            }

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $realQuestion = new Question('', $default);
            $realQuestion->setAutocompleterValues($autocomplete);
            $realQuestion->setTrimmable(true);
            $response = StringUtil::trim(
                $helper->ask($this->input(), $this->output(), $realQuestion) ?? $default ?? '',
            );

            $valid = true;
            $validCb = true;

            if ($cb !== null) {
                $validCb = $cb($response);
            }
            if ($validResponses !== null) {
                $valid = in_array($response, $validResponses, true);
            }

            if ($default === null && $response === '') {
                $valid = false;
            }
            $repeatQuestion = $response === '?';
        } while (!($valid && $validCb));

        return StringUtil::trim($response);
    }

    protected function beep(): void
    {
        $this->output()->write("\007");
    }

    protected function bus(): BusInterface
    {
        return $this->get(BusInterface::class);
    }

    protected function clear(): void
    {
        $this->output()->write("\033\143");
    }

    protected function clearPreviousLine(): void
    {
        $this->cursor()->moveUp();
        $this->cursor()->clearLine();
    }

    protected function copy(string $text): bool
    {
        $status = 0;
        exec("echo '{$text}' | pbcopy &> /dev/null", result_code: $status);

        return $status === 0;
    }

    protected function cursor(): Cursor
    {
        return new Cursor($this->output());
    }

    protected function dispatchCommand(
        Command $command,
        bool $async = false,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $this->bus()->dispatchCommand($command, $async ? 'system' : null, $delayMs, $retryOptions);
    }

    /**
     * @template T of Document
     * @param Query<T> $query
     * @return T
     */
    protected function dispatchQuery(
        Query $query,
    ): DocumentInterface {
        return $this->bus()->dispatchQuery($query);
    }

    /**
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $colors = ['red', 'blue', 'green', 'black', 'yellow', 'magenta', 'cyan', 'white'];

        foreach ($colors as $color) {
            $output->getFormatter()->setStyle("{$color}", new OutputFormatterStyle("{$color}", null, []));
            $output->getFormatter()->setStyle("{$color}1", new OutputFormatterStyle("{$color}", null, ['bold']));
            $output->getFormatter()->setStyle("{$color}2", new OutputFormatterStyle("{$color}", null, ['underscore']));
            $output->getFormatter()->setStyle("{$color}3", new OutputFormatterStyle("{$color}", null, ['blink']));
            $output->getFormatter()->setStyle("{$color}4", new OutputFormatterStyle("{$color}", null, ['reverse']));
            $output->getFormatter()->setStyle("{$color}5", new OutputFormatterStyle("{$color}", null, ['conceal']));
        }

        $this->play();

        return 0;
    }

    protected function forceEnterToContinue(): void
    {
        $this->output()->write('<white2>[ENTER to continue]</white2>');
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $realQuestion = new Question('');
        $realQuestion->setTrimmable(true);
        $helper->ask($this->input(), $this->output(), $realQuestion);
    }

    protected function input(): InputInterface
    {
        if ($this->input === null) {
            throw new BaseException('play method has not been executed.');
        }

        return $this->input;
    }

    protected function output(): OutputInterface
    {
        if ($this->output === null) {
            throw new BaseException('play method has not been executed.');
        }

        return $this->output;
    }

    abstract protected function play(): void;

    protected function readArgument(string $name): string
    {
        return $this->input()->getArgument($name);
    }
}
