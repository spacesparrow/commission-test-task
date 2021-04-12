<?php

declare(strict_types=1);

namespace App\CommissionTask\Exception;

use UnexpectedValueException;

/**
 * Class UnsupportedPersonTypeException
 * Will be thrown if provided person type is not listed in config.
 */
class UnsupportedPersonTypeException extends UnexpectedValueException
{
    /** @var string */
    protected $message = 'Unsupported person type was provided %s';

    /**
     * UnsupportedPersonTypeException constructor.
     */
    public function __construct(string $type)
    {
        $this->message = sprintf($this->message, $type);

        parent::__construct($this->message);
    }
}
