<?php

declare(strict_types=1);

namespace Xgc\Dto;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Xgc\Exception\BaseException;

use function count;
use function in_array;

class ThrowableDocument implements DocumentInterface
{
    /**
     * @var string[]
     */
    private static array $validPrefixTraceFiles = [];

    public readonly int $status;

    public readonly Throwable $throwable;

    public function __construct(
        Throwable $throwable
    ) {
        if ($throwable instanceof BaseException) {
            $this->status = $throwable->status;
        } else {
            $this->status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->throwable = $throwable;
    }

    public static function addValidPrefixTraceFile(string $filePrefix): void
    {
        if (in_array($filePrefix, self::$validPrefixTraceFiles, true)) {
            return;
        }

        self::$validPrefixTraceFiles[] = $filePrefix;
    }

    public function toArray(?ContextInterface $context): array
    {
        $message = $this->throwable->getMessage();
        $extras = null;

        if ($this->throwable instanceof BaseException) {
            $extras = $this->throwable->extras;
        }

        if ($this->status >= 500 && $context?->isProd() === true) {
            $extras = null;
            $message = 'Internal Server Error';
        }

        $data = [
            'extras' => $extras,
            'message' => $message,
        ];

        if ($context?->isDebug() === true) {
            $data['file'] = str_replace('/app/', '', $this->throwable->getFile());
            $data['line'] = $this->throwable->getLine();
            $data['trace'] = $this->trace();
        }

        return $data;
    }

    /**
     * @return array{
     *     file: string,
     *     line: int
     * }[]
     */
    public function trace(): array
    {
        $trace = $this->throwable->getTrace();
        $finalTrace = [];
        foreach ($trace as $item) {
            $file = $item['file'] ?? '';
            $line = $item['line'] ?? 0;

            if (count(self::$validPrefixTraceFiles) === 0) {
                $isValid = true;
            } else {
                $isValid = false;
                foreach (self::$validPrefixTraceFiles as $prefix) {
                    if (str_starts_with($file, $prefix)) {
                        $isValid = true;

                        break;
                    }
                }
            }
            if ($isValid) {
                $finalTrace[] = [
                    'file' => $file,
                    'line' => $line,
                ];
            }
        }

        return $finalTrace;
    }
}
