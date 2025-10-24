<?php

declare(strict_types=1);

namespace Xgc\Symfony\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentInterface;
use Xgc\Exception\BaseException;
use Xgc\Message\BusInterface;
use Xgc\Message\Command;
use Xgc\Message\Query;
use Xgc\Message\RetryStamp;
use Xgc\Symfony\Service\OutputService;
use Xgc\Utils\ContainerBoxTrait;

abstract class Console extends BaseCommand
{
    use ContainerBoxTrait;

    private ?InputInterface $input;

    private OutputService $output;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->input = null;
        $this->output = new OutputService('.');
    }

    protected function beep(): void
    {
        $this->output()->write("\007");
    }

    protected function output(): OutputService
    {
        return $this->output;
    }

    protected function clear(): void
    {
        $this->output()->clearLine();
    }

    protected function clearPreviousLine(): void
    {
        $this->output()->clearPreviousLine();
    }

    protected function copy(string $text): bool
    {
        $status = 0;
        exec("echo '{$text}' | pbcopy &> /dev/null", result_code: $status);

        return $status === 0;
    }

    protected function dispatchCommand(
        Command $command,
        bool $async = false,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $this->bus()->dispatchCommand($command, $async ? 'system' : null, $delayMs, $retryOptions);
    }

    protected function bus(): BusInterface
    {
        return $this->get(BusInterface::class);
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
        $this->output->assignOutput($output);

        $colors = ['red', 'blue', 'green', 'black', 'yellow', 'magenta', 'cyan', 'white'];

        foreach ($colors as $color) {
            $output->getFormatter()->setStyle($color, new OutputFormatterStyle($color, null, []));
            $output->getFormatter()->setStyle("{$color}1", new OutputFormatterStyle($color, null, ['bold']));
            $output->getFormatter()->setStyle("{$color}2", new OutputFormatterStyle($color, null, ['underscore']));
            $output->getFormatter()->setStyle("{$color}3", new OutputFormatterStyle($color, null, ['blink']));
            $output->getFormatter()->setStyle("{$color}4", new OutputFormatterStyle($color, null, ['reverse']));
            $output->getFormatter()->setStyle("{$color}5", new OutputFormatterStyle($color, null, ['conceal']));
        }

        $this->play();

        return 0;
    }

    abstract protected function play(): void;

    protected function readArgument(string $name): string
    {
        return $this->input()->getArgument($name);
    }

    protected function input(): InputInterface
    {
        if ($this->input === null) {
            throw new BaseException('play method has not been executed.');
        }

        return $this->input;
    }
}
