<?php

declare(strict_types=1);

namespace App\CommissionTask\Factory;

use App\CommissionTask\Exception\UnsupportedCurrencyException;
use App\CommissionTask\Exception\UnsupportedOperationTypeException;
use App\CommissionTask\Exception\UnsupportedPersonTypeException;
use App\CommissionTask\Model\CashInOperation;
use App\CommissionTask\Model\CashOutOperation;
use App\CommissionTask\Model\Operation;
use Exception;

class OperationFactory
{
    /**
     * @param int $userId
     * @param string $userType
     * @param string $amount
     * @param string $currencyCode
     * @param string $type
     * @param string $date
     * @return Operation
     *
     * @throws Exception
     * @throws UnsupportedOperationTypeException
     * @throws UnsupportedPersonTypeException
     * @throws UnsupportedCurrencyException
     */
    public static function create(
        int $userId,
        string $userType,
        string $amount,
        string $currencyCode,
        string $type,
        string $date = 'now'
    ): Operation {
        switch ($type) {
            case Operation::TYPE_CASH_IN:
                return new CashInOperation(
                    $userId,
                    $userType,
                    $amount,
                    $currencyCode,
                    $date
                );
            case Operation::TYPE_CASH_OUT:
                return new CashOutOperation(
                    $userId,
                    $userType,
                    $amount,
                    $currencyCode,
                    $date
                );
            default:
                throw new UnsupportedOperationTypeException($type);
        }
    }
}