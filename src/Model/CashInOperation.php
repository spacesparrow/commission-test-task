<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;

class CashInOperation extends Operation
{
    public function __construct(
        int $userId,
        string $userType,
        string $amount,
        string $currencyCode,
        string $date = 'now'
    ) {
        parent::__construct($userId, $userType, $amount, $currencyCode, $date, Operation::TYPE_CASH_IN);
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