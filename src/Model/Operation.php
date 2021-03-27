<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\AppConfig;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use DateTime;

class Operation
{
    const TYPE_CASH_IN = 'cash_in';
    const TYPE_CASH_OUT = 'cash_out';

    /** @var string */
    private $type;

    /** @var DateTime */
    private $date;

    public function __construct(string $type, string $date = 'now')
    {
        $this->checkType($type);

        $this->type = $type;
        $this->date = new DateTime($date);
    }

    private function checkType(string $type)
    {
        if (!in_array($type, AppConfig::getInstance()->get('operations.types'), true)) {
            throw new UnsupportedOperationTypeException($type);
        }
    }
}
