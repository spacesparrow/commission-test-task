<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

class UnsupportedCurrencyException extends UnexpectedValueException
{
    protected $message = 'Unsupported currency was provided';

    public function __construct(string $currency)
    {
        $this->message .= " $currency";

        parent::__construct($this->message);
    }
}