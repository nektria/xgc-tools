<?php

declare(strict_types=1);

namespace Xgc\Symfony\Console\Debug;

use Symfony\Component\Console\Input\InputOption;
use Xgc\Dto\ContextInterface;
use Xgc\Exception\BaseException;
use Xgc\Symfony\Console\Console;
use Xgc\Utils\FileUtil;

class SetupMessageConsole extends Console
{
    public function __construct(
        private readonly ContextInterface $context
    ) {
        parent::__construct('debug:setup:message');
    }

    protected function configure(): void
    {
        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'Query or Command.',
            '',
        );

        $this->addOption(
            'message',
            'm',
            InputOption::VALUE_REQUIRED,
            'Message name.',
            '',
        );

        $this->addOption(
            'resource',
            'r',
            InputOption::VALUE_REQUIRED,
            'Resource name.',
            '',
        );

        $this->addOption(
            'collection',
            'c',
            InputOption::VALUE_NONE,
            'if it is a collection message.',
            '',
        );
    }

    protected function play(): void
    {
        $isCollection = (bool) $this->input()->getOption('collection');

        $resource = (string) $this->input()->getOption('resource');
        if ($resource === '') {
            throw new BaseException('Resource name is required.');
        }

        $type = (string) $this->input()->getOption('type');
        if ($type === '') {
            throw new BaseException('Message type is required.');
        }

        $message = (string) $this->input()->getOption('message');
        if ($message === '') {
            throw new BaseException('Message is required.');
        }

        if (!is_dir("./src/Message/{$resource}")) {
            mkdir("./src/Message/{$resource}");
        }

        if (!is_dir("./src/MessageHandler/{$resource}")) {
            mkdir("./src/MessageHandler/{$resource}");
        }

        if ($type === 'Query') {
            $fromPath = 'vendor/nektria/php-sdk/assets/message/Query';
            $fromPathHandler = 'vendor/nektria/php-sdk/assets/message/QueryHandler';
        } elseif ($type === 'Command') {
            $fromPath = 'vendor/nektria/php-sdk/assets/message/Command';
            $fromPathHandler = 'vendor/nektria/php-sdk/assets/message/CommandHandler';
        } elseif ($type === 'Event') {
            $fromPath = 'vendor/nektria/php-sdk/assets/message/Event';
            $fromPathHandler = 'vendor/nektria/php-sdk/assets/message/EventHandler';
        } else {
            throw new BaseException('Message type is invalid.');
        }

        if ($isCollection) {
            $this->copyFile(
                "{$fromPath}/__MESSAGE__Collection.php",
                "./src/Message/{$resource}/{$message}.php"
            );
            $this->copyFile(
                "{$fromPathHandler}/__MESSAGE__CollectionHandler.php",
                "./src/MessageHandler/{$resource}/{$message}Handler.php"
            );
        } else {
            $this->copyFile("{$fromPath}/__MESSAGE__.php", "./src/Message/{$resource}/{$message}.php");
            $this->copyFile("{$fromPath}/__MESSAGE__Handler.php", "./src/Message/{$resource}/{$message}Handler.php");
        }
    }

    private function copyFile(string $from, string $to): void
    {
        FileUtil::write($to, $this->fix(FileUtil::read($from)));
    }

    private function fix(string $text): string
    {
        $resource = (string) $this->input()->getOption('resource');
        $message = (string) $this->input()->getOption('message');

        $camelCaseResource = lcfirst($resource);
        $snakeCaseResource = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $resource));
        $hypenCaseResource = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $resource));
        $pathResource = $hypenCaseResource;
        if (!str_ends_with($pathResource, 's')) {
            $pathResource .= 's';
        }

        return str_replace(
            [
                '__PROJECT__',
                '__ENTITY__',
                '__ENTITY_CC__',
                '__ENTITY_SC__',
                '__ENTITY_HC__',
                '__ENTITY_PATH__',
                '__MESSAGE__',
            ],
            [
                $this->context->project(),
                $resource,
                $camelCaseResource,
                $snakeCaseResource,
                $hypenCaseResource,
                $pathResource,
                $message,
            ],
            $text,
        );
    }
}
