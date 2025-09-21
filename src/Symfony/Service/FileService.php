<?php

declare(strict_types=1);

namespace Xgc\Symfony\Service;

use Xgc\Utils\FileUtil;

readonly class FileService
{
    public function __construct(
        private string $projectDir
    ) {
    }

    public function read(string $file): string
    {
        return FileUtil::read($this->normalize($file));
    }

    public function write(string $file, string $content): void
    {
        FileUtil::write($this->normalize($file), $content);
    }

    private function normalize(string $file): string
    {
        if (str_starts_with($file, '/')) {
            return $file;
        }

        return "{$this->projectDir}/{$file}";
    }
}
