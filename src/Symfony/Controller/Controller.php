<?php

declare(strict_types=1);

namespace Xgc\Symfony\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;
use Twig\Environment;
use Xgc\Dto\ArrayDocument;
use Xgc\Dto\ContextInterface;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentInterface;
use Xgc\Exception\BaseException;
use Xgc\Exception\MissingArgumentException;
use Xgc\Message\BusInterface;
use Xgc\Message\Command;
use Xgc\Message\Query;
use Xgc\Message\RetryStamp;
use Xgc\Utils\ArrayDataFetcher;
use Xgc\Utils\ContainerBox;
use Xgc\Utils\JsonUtil;

readonly class Controller
{
    protected Request $request;

    protected ArrayDataFetcher $requestData;

    public function __construct(
        protected ContainerBox $containerBox,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest() ?? new Request();

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

            $this->requestData = new ArrayDataFetcher([...$this->request->query->all(), ...$data]);
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    protected function command(
        Command $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $this->containerBox->get(BusInterface::class)->dispatchCommand($command, $transport, $delayMs, $retryOptions);
    }

    protected function documentResponse(Document $document, int $status = 200): DocumentResponse
    {
        return $this->response($document, $status);
    }

    protected function emptyResponse(): DocumentResponse
    {
        return new DocumentResponse(
            new ArrayDocument(),
            $this->containerBox->get(ContextInterface::class),
            Response::HTTP_NO_CONTENT
        );
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
        return $this->documentResponse($this->containerBox->get(BusInterface::class)->dispatchQuery($query));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters): WebResponse
    {
        $fixedParameters = [];
        foreach ($parameters as $key => $value) {
            if ($value instanceof DocumentInterface) {
                $fixedParameters[$key] = $value->toArray($this->containerBox->get(ContextInterface::class));
            } else {
                $fixedParameters[$key] = $value;
            }
        }

        try {
            return new WebResponse(
                $this->containerBox->get(Environment::class)->render($view, $fixedParameters),
                $fixedParameters
            );
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    protected function response(DocumentInterface $document, int $status = 200): DocumentResponse
    {
        return new DocumentResponse($document, $this->containerBox->get(ContextInterface::class), $status);
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
