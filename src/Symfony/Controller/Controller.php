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
use Xgc\Utils\ContainerBoxTrait;
use Xgc\Utils\JsonUtil;

readonly class Controller
{
    use ContainerBoxTrait;

    protected Request $request;

    protected ArrayDataFetcher $requestData;

    public function __construct(
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

    public function setContainer(ContainerInterface $container): void
    {
        self::CONTAINER->setContainer($container);
    }

    protected function command(
        Command $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        self::CONTAINER->get(BusInterface::class)->dispatchCommand($command, $transport, $delayMs, $retryOptions);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    protected function container(string $class): object
    {
        return self::CONTAINER->get($class);
    }

    protected function documentResponse(Document $document, int $status = 200): DocumentResponse
    {
        return $this->response($document, $status);
    }

    protected function emptyResponse(): DocumentResponse
    {
        return new DocumentResponse(
            new ArrayDocument(),
            self::CONTAINER->get(ContextInterface::class),
            Response::HTTP_NO_CONTENT
        );
    }

    protected function getDeviceType(): string
    {
        $ua = $this->request->headers->get('User-Agent') ?? '';

        if (preg_match('/tablet|ipad|playbook|silk/i', $ua) !== false) {
            return 'tablet';
        }

        if (preg_match('/mobi|android|touch|iphone/i', $ua) !== false) {
            return 'mobile';
        }

        return 'desktop';
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
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    protected function getService(string $class): object
    {
        /** @var T $service */
        $service = self::CONTAINER->get($class);

        return $service;
    }

    /**
     * @template T of Document
     * @param Query<T> $query
     */
    protected function queryResponse(Query $query): DocumentResponse
    {
        return $this->documentResponse(self::CONTAINER->get(BusInterface::class)->dispatchQuery($query));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters = [], bool $ignoreContext = false): WebResponse
    {
        $fixedParameters = [];
        foreach ($parameters as $key => $value) {
            if ($value instanceof DocumentInterface) {
                $fixedParameters[$key] = $value->toArray(
                    $ignoreContext ?
                        null :
                        self::CONTAINER->get(ContextInterface::class)
                );
            } else {
                $fixedParameters[$key] = $value;
            }
        }
        $fixedParameters['__locale__'] = $this->request->getLocale();

        try {
            return new WebResponse(
                self::CONTAINER->get(Environment::class)->render($view, $fixedParameters),
                $fixedParameters
            );
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    protected function response(DocumentInterface $document, int $status = 200): DocumentResponse
    {
        return new DocumentResponse($document, self::CONTAINER->get(ContextInterface::class), $status);
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
