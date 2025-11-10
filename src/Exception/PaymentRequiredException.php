<?php

declare(strict_types=1);

namespace Xgc\Exception;

class PaymentRequiredException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            'Payment is required for this feature',
            status: 402,
            extras: [
                'type' => 'PAYMENT_REQUIRED'
            ]
        );
    }
}
