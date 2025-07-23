<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Xgc\Exception\BaseException;

class FileUtil
{
    public static function read(string $file): string
    {
        $content = file_get_contents($file);
        if ($content === false) {
            throw new BaseException("Cannot read file '{$file}'.", extras: [
                'file' => $file,
            ]);
        }

        return $content;
    }

    public static function write(string $file, string $content): void
    {
        if (file_put_contents($file, $content) === false) {
            throw new BaseException("Cannot write file '{$file}'.", extras: [
                'file' => $file,
            ]);
        }
    }
}
