<?php

declare(strict_types=1);

namespace Xgc\Symfony\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;
use Xgc\Dto\ArrayDocument;
use Xgc\Dto\ContextInterface;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentInterface;
use Xgc\Exception\MissingArgumentException;
use Xgc\Log\ProcessRegistry;
use Xgc\Message\BusInterface;
use Xgc\Message\Command;
use Xgc\Message\Query;
use Xgc\Message\RetryStamp;
use Xgc\Utils\ArrayDataFetcher;
use Xgc\Utils\JsonUtil;

readonly class Controller
{
    protected Request $request;

    protected ArrayDataFetcher $requestData;

    public function __construct(
        protected ProcessRegistry $processRegistry,
        protected ContextInterface $context,
        protected BusInterface $bus,
        protected ContainerInterface $container,
        RequestStack $requestStack,
    ) {
        $this->request = $requestStack->getCurrentRequest() ?? new Request();
        $body = [];

        try {
            $data = [];
            $content = $this->request->getContent();
            if ($content === '') {
                $this->request->request->replace();
            } elseif ($content[0] === '[') {
                $data = JsonUtil::decode($content);
            } else {
                $data = JsonUtil::decode($content);
            }
            $body = $data;
        } catch (Throwable) {
        }
        $this->requestData = new ArrayDataFetcher(array_merge($this->request->query->all(), $body));
    }

    protected function command(
        Command $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $this->bus->dispatchCommand($command, $transport, $delayMs, $retryOptions);
    }

    protected function documentResponse(Document $document, int $status = 200): DocumentResponse
    {
        return $this->response($document, $status);
    }

    protected function emptyResponse(): DocumentResponse
    {
        return new DocumentResponse(new ArrayDocument([]), $this->context, Response::HTTP_NO_CONTENT);
    }

    protected function getFile(string $field): ?string
    {
        /** @var UploadedFile|null $file */
        $file = $this->request->files->get($field);

        if ($file === null) {
            return null;
        }

        return $file->getRealPath();
    }

    /**
     * @template T of Document
     * @param Query<T> $query
     */
    protected function queryResponse(Query $query): DocumentResponse
    {
        return $this->documentResponse($this->bus->dispatchQuery($query));
    }

    protected function response(DocumentInterface $document, int $status = 200): DocumentResponse
    {
        return new DocumentResponse($document, $this->context, $status);
    }

    protected function retrieveFile(string $field): string
    {
        $file = $this->getFile($field);

        if ($file === null) {
            throw new MissingArgumentException($field);
        }

        return $file;
    }
}
