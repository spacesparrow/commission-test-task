<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

class UnexpectedOperationTypeException extends UnexpectedValueException
{
    protected $message = 'Unexpected operation type was provided, passed - %s, allowed - %s';

    public function __construct(string $passed, string $allowed)
    {
        $this->message = sprintf($this->message, $passed, $allowed);

        parent::__construct($this->message);
    }
}