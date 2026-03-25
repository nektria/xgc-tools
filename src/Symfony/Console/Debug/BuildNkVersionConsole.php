<?php

declare(strict_types=1);

namespace Xgc\Symfony\Console\Debug;

use Throwable;
use Xgc\Dto\ContextInterface;
use Xgc\Dto\LocalClock;
use Xgc\Symfony\Console\Console;
use Xgc\Utils\JsonUtil;
use Xgc\Utils\StringUtil;

class BuildNkVersionConsole extends Console
{
    public function __construct(
        private readonly ContextInterface $context,
    ) {
        parent::__construct('debug:version');
    }

    protected function configure(): void
    {
        $this->addArgument('branch');
    }

    protected function play(): void
    {
        try {
            $commit = substr(StringUtil::trim((string) exec('git rev-parse HEAD')), 0, 7);
            $total = exec('git rev-list --count HEAD');
            /** @var string $branch */
            $branch = $this->input()->getArgument('branch') ?? exec('git rev-parse --abbrev-ref HEAD');
            $version = $branch === 'main' ? "v{$total}" : "v{$total}-{$branch}";

            $this->output()->writeln(JsonUtil::encode([
                'builtAt' => LocalClock::now('Europe/Madrid')->dateTimeString(),
                'hash' => $commit,
                'project' => $this->context->project(),
                'type' => 'Release',
                'version' => $version,
            ], true));
        } catch (Throwable) {
            $this->output()->writeln(JsonUtil::encode([
                'builtAt' => LocalClock::now('Europe/Madrid')->dateTimeString(),
                'hash' => '',
                'project' => $this->context->project(),
                'type' => 'Development',
                'version' => '',
            ], true));
        }
    }
}
