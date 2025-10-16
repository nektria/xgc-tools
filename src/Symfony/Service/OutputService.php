<?php

declare(strict_types=1);

namespace Xgc\Symfony\Service;

use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;
use Xgc\Dto\Clock;

use const FILE_APPEND;
use const PHP_EOL;

class OutputService
{
    private ?Cursor $cursor;

    private readonly string $logFile;

    private ?OutputInterface $output;

    public function __construct()
    {
        $createdAt = Clock::now()->toLocal('Europe/Madrid');
        $this->logFile = "tmp/{$createdAt->dateTimeString()}.log";
        $this->cursor = null;
        $this->output = null;
    }

    public function assignOutput(OutputInterface $outputInterface): void
    {
        $this->output = $outputInterface;
        $this->cursor = new Cursor($this->output);
    }

    public function log(string | int | float | bool $output): void
    {
        $this->writeln($this->fixMessage($output));
    }

    public function writeln(string | int | float | bool $output): void
    {
        $this->write($this->fixMessage($output) . PHP_EOL);
    }

    public function write(string | int | float | bool $output): void
    {
        $output = $this->fixMessage($output);

        $now = Clock::now()->toLocal('Europe/Madrid');
        $cleanOutput = preg_replace('/<\/?\w+\d*>/', '', ['']);

        $formattedOutput = "[{$now->microDateTimeString()}] {$cleanOutput}";
        file_put_contents($this->logFile, $formattedOutput, FILE_APPEND);

        if ($this->output !== null) {
            file_put_contents($this->logFile, $output, FILE_APPEND);
        }
    }

    private function fixMessage(string | int | float | bool $output): string
    {
        if ($output === true) {
            $output = 'true';
        }

        if ($output === false) {
            $output = 'false';
        }

        return (string) $output;
    }

    public function info(string | int | float | bool $output): void
    {
        $this->writeln("<info>{$this->fixMessage($output)}</info>");
    }

    public function warning(string | int | float | bool $output): void
    {
        $this->writeln("<warning>{$this->fixMessage($output)}</warning>");
    }

    public function error(string | int | float | bool $output): void
    {
        $this->writeln("<error>{$this->fixMessage($output)}</error>");
    }

    public function clearPreviousLine(bool $clearCurrentLine = true): void
    {
        if ($this->cursor === null) {
            return;
        }

        if ($clearCurrentLine) {
            $this->cursor->clearLine();
        }

        $this->cursor->moveUp();
        $this->cursor->clearLine();
    }

    public function clearLine(): void
    {
        if ($this->cursor === null) {
            return;
        }

        $this->cursor->clearLine();
    }
}
