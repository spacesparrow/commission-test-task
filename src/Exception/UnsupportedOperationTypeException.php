<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

/**
 * Class UnsupportedOperationTypeException
 * Will be thrown if provided operation type is not listed in config.
 */
class UnsupportedOperationTypeException extends UnexpectedValueException
{
    /** @var string */
    protected $message = 'Unsupported operation type was provided %s';

    /**
     * UnsupportedOperationTypeException constructor.
     */
    public function __construct(string $type)
    {
        $this->message = sprintf($this->message, $type);

        parent::__construct($this->message);
    }
}
