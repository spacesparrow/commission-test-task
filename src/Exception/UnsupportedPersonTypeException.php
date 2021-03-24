<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

class UnsupportedPersonTypeException extends UnexpectedValueException
{
    protected $message = 'Unsupported person type was provided';

    public function __construct(string $type)
    {
        $this->message .= " {$type}";

        parent::__construct($this->message);
    }
}