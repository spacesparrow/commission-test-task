<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

class UnsupportedOperationTypeException extends UnexpectedValueException
{
    protected $message = 'Unsupported operation type was provided %s';

    public function __construct(string $type)
    {
        $this->message = sprintf($this->message, $type);

        parent::__construct($this->message);
    }
}
