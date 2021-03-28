<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Exception\UnexpectedOperationTypeException;
use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;

class CashInOperation extends Operation
{
    public function __construct(
        int $userId,
        string $userType,
        string $amount,
        string $currencyCode,
        string $type,
        string $date = 'now'
    ) {
        if ($type !== Operation::TYPE_CASH_IN) {
            throw new UnexpectedOperationTypeException($type, Operation::TYPE_CASH_IN);
        }

        parent::__construct($userId, $userType, $amount, $currencyCode, $type, $date);
    }

    public function validateCommission(BigDecimal $actualCommission): BigDecimal
    {
        $allowedCommissionBase = $this->config->get('commissions.cash_in.max_amount');
        $limitedCommissionConverted = Currency::convert(
            $allowedCommissionBase,
            Currency::EUR,
            $this->currency->getCurrencyCode()
        );

        return $actualCommission->isLessThanOrEqualTo($limitedCommissionConverted)
            ? $actualCommission
            : $limitedCommissionConverted;
    }
}