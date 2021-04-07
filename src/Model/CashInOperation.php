<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;
use Brick\Money\Money;

class CashInOperation extends Operation
{
    public function __construct(
        int $userId,
        string $userType,
        string $amount,
        string $currencyCode,
        int $sequenceNumber,
        Money $alreadyUserThisWeek,
        string $date = 'now'
    ) {
        parent::__construct(
            $userId,
            $userType,
            $amount,
            $currencyCode,
            $date,
            Operation::TYPE_CASH_IN,
            $sequenceNumber,
            $alreadyUserThisWeek
        );
    }

    protected function validateCommission(BigDecimal $actualCommission): BigDecimal
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

    protected function getAmountForCommission(): BigDecimal
    {
        return $this->amount;
    }
}
