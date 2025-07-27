<?php

declare(strict_types=1);

namespace Xgc\Symfony\Console\Debug;

use Throwable;
use Xgc\Exception\BaseException;
use Xgc\Symfony\Console\Console;

use function in_array;

class ValidateMessagesHandler extends Console
{
    public function __construct()
    {
        parent::__construct('debug:messages:validate');
    }

    protected function play(): void
    {
        try {
            $this->validateFolder1('src/Message');
            $this->validateFolder2('src/MessageHandler');
        } catch (Throwable $e) {
            $this->output()->writeln("<red>{$e->getMessage()}</red>");
        }
    }

    private function validateFolder1(string $folder): void
    {
        $files = scandir($folder);
        $dest = str_replace('/Message', '/MessageHandler', $folder);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("{$folder}/{$file}")) {
                $this->validateFolder1("{$folder}/{$file}");

                continue;
            }

            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $ignoredFiles = ['Command.php', 'Event.php', 'MessageHandler.php', 'Query.php'];
            if (in_array($file, $ignoredFiles, true)) {
                continue;
            }

            if (is_file("{$folder}/{$file}")) {
                $destFile = str_replace('.php', 'Handler.php', $file);
                if (!file_exists("{$dest}/{$destFile}")) {
                    throw new BaseException("Handler file for message {$folder}/{$file} is missing.");
                }
            }
        }
    }

    private function validateFolder2(string $folder): void
    {
        $files = scandir($folder);
        $dest = str_replace('/MessageHandler', '/Message', $folder);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("{$folder}/{$file}")) {
                $this->validateFolder2("{$folder}/{$file}");

                continue;
            }

            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $ignoredFiles = ['Command.php', 'Event.php', 'MessageHandler.php', 'Query.php'];
            if (in_array($file, $ignoredFiles, true)) {
                continue;
            }

            if (is_file("{$folder}/{$file}")) {
                $destFile = str_replace('Handler.php', '.php', $file);

                if (!file_exists("{$dest}/{$destFile}")) {
                    throw new BaseException("Message file for handler {$folder}/{$file} is missing.");
                }
            }
        }
    }
}
