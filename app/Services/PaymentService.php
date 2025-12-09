<?php

namespace App\Services;

use App\Interfaces\PaymentMethodInterface;

class PaymentService
{
    protected PaymentMethodInterface $gateway;

    public function __construct(PaymentMethodInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function pay($data)
    {
        return $this->gateway->pay($data);
    }

    public function callback($transactionId)
    {
        return $this->gateway->callback($transactionId);
    }
}
