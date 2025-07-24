<?php

declare(strict_types=1);

namespace Xgc\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use Xgc\Exception\BaseException;
use Xgc\Exception\RequestException;
use Xgc\Utils\FileUtil;
use Xgc\Utils\JsonUtil;
use Xgc\Utils\StringUtil;

use function is_string;

/**
 * @phpstan-type RequestOptions array{
 *     errorIfFails?: bool,
 * }
 */
readonly class RequestClient
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    /**
     * @param array<string, string|int|bool|float> $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    public function delete(string $url, array $data = [], array $headers = [], array $options = []): RequestResponse
    {
        return $this->request(
            'DELETE',
            $url,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @param mixed[] $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    public function file(
        string $url,
        string $filename,
        array $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        return $this->fileRequest(
            $url,
            $filename,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @param mixed[] $data
     * @param array<string, string> $headers
     * @param array<string, string> $filenames
     * @param RequestOptions $options
     */
    public function files(
        string $url,
        array $filenames,
        array $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        return $this->filesRequest(
            $url,
            $filenames,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @param array<string, string|int|bool|float> $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    public function get(
        string $url,
        array $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        return $this->request(
            'GET',
            $url,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @param mixed[]|string $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    public function patch(
        string $url,
        array | string $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        return $this->request(
            'PATCH',
            $url,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @param mixed[]|string $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    public function post(
        string $url,
        array | string $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        return $this->request(
            'POST',
            $url,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @param mixed[]|string $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    public function put(
        string $url,
        array | string $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        return $this->request(
            'PUT',
            $url,
            data: $data,
            headers: $headers,
            options: $options,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [];
    }

    /**
     * @param mixed[] $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    private function fileRequest(
        string $url,
        string $filename,
        array $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        $body = FileUtil::read($filename);
        $contentType = mime_content_type($filename);

        $defaultHeaders = $this->defaultHeaders();
        $defaultHeaders['Content-Type'] = $contentType;
        $defaultHeaders['Content-Length'] = filesize($filename);
        $headers = array_merge($defaultHeaders, $headers);

        $requestOptions = [
            'verify_peer' => false,
            'verify_host' => false,
            'headers' => $headers,
        ];

        try {
            $params = '';
            foreach ($data as $key => $value) {
                if ($value === true) {
                    $value = 'true';
                } elseif ($value === false) {
                    $value = 'false';
                }
                if ($params !== '') {
                    $params .= '&';
                }
                $params .= "{$key}={$value}";
            }
            if ($params !== '') {
                $url .= "?{$params}";
            }

            $requestOptions['body'] = $body;

            $response = $this->client->request(
                'POST',
                $url,
                $requestOptions,
            );

            $content = $response->getContent(false);
            $status = $response->getStatusCode();
            $respHeaders = $response->getHeaders(false);

            $cookies = [];

            foreach ($response->getInfo()['response_headers'] as $header) {
                $headerParts = explode(':', $header);

                if ($headerParts[0] === 'Set-Cookie') {
                    $parts = explode(';', $headerParts[1]);
                    $cookie = explode('=', $parts[0]);
                    $cookies[StringUtil::trim($cookie[0])] = StringUtil::trim($cookie[1]);
                }
            }

            $response = new RequestResponse(
                'POST',
                $url,
                $status,
                $content,
                $headers,
                $respHeaders,
                $cookies
            );
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }

        if ($status >= 400 && ($options['errorIfFails'] ?? true)) {
            throw new RequestException($response);
        }

        return $response;
    }

    /**
     * @param mixed[] $data
     * @param array<string, string> $headers
     * @param array<string, string> $filenames
     * @param RequestOptions $options
     */
    private function filesRequest(
        string $url,
        array $filenames,
        array $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        $body = [];
        foreach ($filenames as $key => $filename) {
            $body[$key] = FileUtil::read($filename);
        }

        $headers = array_merge($this->defaultHeaders(), $headers);

        $requestOptions = [
            'verify_peer' => false,
            'verify_host' => false,
            'headers' => $headers,
        ];

        try {
            $params = '';
            foreach ($data as $key => $value) {
                if ($value === true) {
                    $value = 'true';
                } elseif ($value === false) {
                    $value = 'false';
                }
                if ($params !== '') {
                    $params .= '&';
                }
                $params .= "{$key}={$value}";
                $body[$key] = $value;
            }

            if ($params !== '') {
                $url .= "?{$params}";
            }

            $requestOptions['body'] = $body;

            $response = $this->client->request(
                'POST',
                $url,
                $requestOptions,
            );

            $cookies = [];

            foreach ($response->getInfo()['response_headers'] as $header) {
                $headerParts = explode(':', $header);

                if ($headerParts[0] === 'Set-Cookie') {
                    $parts = explode(';', $headerParts[1]);
                    $cookie = explode('=', $parts[0]);
                    $cookies[StringUtil::trim($cookie[0])] = StringUtil::trim($cookie[1]);
                }
            }

            $content = $response->getContent(false);
            $status = $response->getStatusCode();
            $respHeaders = $response->getHeaders(false);

            $response = new RequestResponse(
                'POST',
                $url,
                $status,
                $content,
                $headers,
                $respHeaders,
                $cookies
            );
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }

        if ($status >= 400 && ($options['errorIfFails'] ?? true)) {
            throw new RequestException($response);
        }

        return $response;
    }

    /**
     * @param mixed[]|string $data
     * @param array<string, string> $headers
     * @param RequestOptions $options
     */
    private function request(
        string $method,
        string $url,
        array | string $data = [],
        array $headers = [],
        array $options = [],
    ): RequestResponse {
        $headers = array_merge($this->defaultHeaders(), $headers);

        $requestOptions = [
            'verify_peer' => false,
            'verify_host' => false,
            'headers' => $headers,
        ];

        if ($method === 'POST' || $method === 'PATCH') {
            if (is_string($data)) {
                $body = $data;
            } else {
                $body = JsonUtil::encode($data);
            }
            $requestOptions['body'] = $body;
        } elseif (is_string($data)) {
            $url .= "?{$data}";
        } else {
            $params = '';
            foreach ($data as $key => $value) {
                if ($value === true) {
                    $value = 'true';
                } elseif ($value === false) {
                    $value = 'false';
                }
                if ($params !== '') {
                    $params .= '&';
                }
                $params .= "{$key}={$value}";
            }
            if ($params !== '') {
                $url .= "?{$params}";
            }
        }

        try {
            $response = $this->client->request(
                $method,
                $url,
                $requestOptions,
            );

            $content = $response->getContent(false);
            $status = $response->getStatusCode();
            $respHeaders = $response->getHeaders(false);

            $cookies = [];

            foreach ($response->getInfo()['response_headers'] as $header) {
                $headerParts = explode(':', $header);

                if ($headerParts[0] === 'Set-Cookie') {
                    $parts = explode(';', $headerParts[1]);
                    $cookie = explode('=', $parts[0]);
                    $cookies[StringUtil::trim($cookie[0])] = StringUtil::trim($cookie[1]);
                }
            }

            $response = new RequestResponse(
                $method,
                $url,
                $status,
                $content,
                $headers,
                $respHeaders,
                $cookies,
            );
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }

        if ($status >= 400 && ($options['errorIfFails'] ?? true)) {
            throw new RequestException($response);
        }

        return $response;
    }
}
