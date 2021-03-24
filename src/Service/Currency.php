<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedCurrencyException;

class Currency
{
    public const EUR = 'EUR';
    public const USD = 'USD';
    public const JPY = 'JPY';

    /** @var string */
    private $code;

    public function __construct(string $code)
    {
        $this->checkCurrencySupported($code);

        $this->code = $code;
    }

    private function checkCurrencySupported(string $code): void
    {
        if (!in_array($code, AppConfig::getInstance()->get('currencies.supported'), true)) {
            throw new UnsupportedCurrencyException($code);
        }
    }
}