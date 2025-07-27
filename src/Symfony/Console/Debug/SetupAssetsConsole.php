<?php

declare(strict_types=1);

namespace Xgc\Symfony\Console\Debug;

use Xgc\Dto\ContextInterface;
use Xgc\Exception\BaseException;
use Xgc\Symfony\Console\Console;
use Xgc\Utils\FileUtil;

class SetupAssetsConsole extends Console
{
    public function __construct(
        private readonly ContextInterface $context,
    ) {
        parent::__construct('debug:setup:assets');
    }

    protected function play(): void
    {
        $this->copyDir('vendor/nektria/php-tools/assets/setup', '.');
        $this->output()->writeln('done');

        exec('chmod -R +x bin/*');
    }

    private function copyDir(string $from, string $to): void
    {
        $files = scandir($from);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $toFile = $this->fix($file);

            if ($toFile === '.git-ignore') {
                $toFile = '.gitignore';
            }

            $fromPath = "{$from}/{$file}";
            $toPath = "{$to}/{$toFile}";

            if (is_dir($fromPath)) {
                if (!is_dir($toPath) && !mkdir($toPath)) {
                    throw new BaseException("Directory '{$toPath}' was not created.");
                }
                $this->copyDir($fromPath, $toPath);
            } else {
                $this->copyFile($fromPath, $toPath);
            }
        }
    }

    private function copyFile(string $from, string $to): void
    {
        FileUtil::write($to, $this->fix(FileUtil::read($from)));
    }

    private function fix(string $text): string
    {
        return str_replace('__PROJECT__', $this->context->project(), $text);
    }
}
