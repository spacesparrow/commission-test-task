<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

/**
 * Class UnsupportedCurrencyException
 * Will be thrown if provided currency is not listed in config.
 */
class UnsupportedCurrencyException extends UnexpectedValueException
{
    /** @var string */
    protected $message = 'Unsupported currency was provided %s';

    /**
     * UnsupportedCurrencyException constructor.
     */
    public function __construct(string $currency)
    {
        $this->message = sprintf($this->message, $currency);

        parent::__construct($this->message);
    }
}
