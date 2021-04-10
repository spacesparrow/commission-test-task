<?php

declare(strict_types=1);

namespace App\CommissionTask\Factory;

use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Model\CashInOperation;
use App\CommissionTask\Model\CashOutOperation;
use App\CommissionTask\Model\Operation;
use Brick\Money\Money;
use Exception;

/**
 * Class OperationFactory
 * @package App\CommissionTask\Factory
 */
class OperationFactory
{
    /**
     * @param int $personId
     * @param string $personType
     * @param string $amount
     * @param string $currencyCode
     * @param string $type
     * @param string $date
     * @param int $sequenceNumber
     * @param Money $usedThisWeek
     * @return Operation
     * @throws Exception
     * @throws UnsupportedOperationTypeException
     * @throws UnsupportedPersonTypeException
     * @throws UnsupportedCurrencyException
     */
    public static function create(
        int $personId,
        string $personType,
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
                    $personId,
                    $personType,
                    $amount,
                    $currencyCode,
                    $sequenceNumber,
                    $usedThisWeek,
                    $date
                );
            case Operation::TYPE_CASH_OUT:
                return new CashOutOperation(
                    $personId,
                    $personType,
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
