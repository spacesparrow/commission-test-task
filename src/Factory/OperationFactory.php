<?php

declare(strict_types=1);

namespace App\CommissionTask\Factory;

use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Model\CashInOperation;
use App\CommissionTask\Model\CashOutOperation;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Person;
use Brick\Money\Money;
use Exception;

/**
 * Class OperationFactory.
 */
class OperationFactory
{
    /**
     * @throws Exception
     * @throws UnsupportedOperationTypeException
     * @throws UnsupportedPersonTypeException
     * @throws UnsupportedCurrencyException
     */
    public static function create(
        Person $person,
        string $amount,
        string $currencyCode,
        string $type,
        int $sequenceNumber,
        Money $usedThisWeek,
        string $date = 'now'
    ): Operation {
        switch ($type) {
            case Operation::TYPE_CASH_IN:
                return new CashInOperation(
                    $person,
                    $amount,
                    $currencyCode,
                    $sequenceNumber,
                    $usedThisWeek,
                    $date
                );
            case Operation::TYPE_CASH_OUT:
                return new CashOutOperation(
                    $person,
                    $amount,
                    $currencyCode,
                    $sequenceNumber,
                    $usedThisWeek,
                    $date
                );
            default:
                throw new UnsupportedOperationTypeException($type);
        }
    }
}
