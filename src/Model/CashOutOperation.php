<?php

declare(strict_types=1);

namespace App\CommissionTask\Model;

use App\CommissionTask\Service\Currency;
use Brick\Math\BigDecimal;

class CashOutOperation extends Operation
{
    public function __construct(
        int $userId,
        string $userType,
        string $amount,
        string $currencyCode,
        string $date = 'now'
    ) {
        parent::__construct($userId, $userType, $amount, $currencyCode, $date, Operation::TYPE_CASH_OUT);
    }

    public function validateCommission(BigDecimal $actualCommission): BigDecimal
    {
        if ($this->user->getType() === Person::TYPE_LEGAL) {
            $allowedCommissionBase =
                $this->config->get("commissions.{$this->type}.min_legal_person_amount");
            $limitedCommissionConverted = Currency::convert(
                $allowedCommissionBase,
                Currency::EUR,
                $this->currency->getCurrencyCode()
            );

            return $actualCommission->isGreaterThanOrEqualTo($limitedCommissionConverted)
                ? $actualCommission
                : $limitedCommissionConverted;
        }
    }
}