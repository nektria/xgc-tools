<?php

declare(strict_types=1);

namespace Xgc\Exception;

use Xgc\Client\RequestResponse;

class RequestException extends BaseException
{
    public function __construct(
        public private(set) readonly RequestResponse $response,
    ) {
        parent::__construct(
            message: "Request Failed: {$this->response->status} {$this->response->method} {$this->response->url}.",
        );
    }
}
